/**
 * @file
 * Main client-side processing for smart content.
 */

(function (Drupal, drupalSettings) {

  // todo: replace generic type for all with 'type' property only for typed
  // conditions.
  // todo: Do I even need to attach these to Drupal?
  Drupal.smartContent = Drupal.smartContent || {};
  Drupal.smartContent.plugin = Drupal.smartContent.plugin || {};
  Drupal.smartContent.plugin.ConditionType = Drupal.smartContent.plugin.ConditionType || {};
  Drupal.smartContent.plugin.Condition = Drupal.smartContent.plugin.Condition || {};
  Drupal.smartContent.plugin.Field = Drupal.smartContent.plugin.Field || {};
  /**
   * Initialize Smart Content processing based on settings.
   *
   * @param settings
   */
  Drupal.smartContent.init = function (settings) {
    Drupal.smartContent.SmartContentManager.processSegments(settings.segments);
    Drupal.smartContent.SmartContentManager.processDecisions(settings.decisions);
  };

  /**
   * Manages segment promises.
   *
   * Provides a central means of storing segment promises so that redundant
   * processing of identical segments share the same segment instead of
   * processing separately.
   */
  Drupal.smartContent.segmentManager = {
    promises: {},
    register: function (segment) {
      if (!this.promises.hasOwnProperty(segment.uuid)) {
        let conditions = [];
        Object.keys(segment.conditions).forEach(key => {
          let promise = Drupal.smartContent.processCondition(segment.conditions[key])
          conditions.push(promise);
        });

        this.promises[segment.uuid] = Promise.all(conditions).then(values => {
          for (let i = 0; i < values.length; i++) {
            if (!values[i]) {
              return false;
            }
          }
          return true;
        }).catch(err => {

        });
      }
      return this.promises[segment.uuid];
    },
    lookup: function (segment_id) {
      if (this.promises.hasOwnProperty(segment_id)) {
        return this.promises[segment_id];
      }
    }
  };

  /**
   * Manage condition field promises.
   *
   * Provides a central means for storing condition field promises so that
   * multiple conditions can depend on the same fields results.  This avoids
   * looking up the same field data multiple times.
   */
  Drupal.smartContent.conditionFieldManager = {
    promises: {},
    register: function (condition) {
      let unique = (condition.field.unique || condition.field.unique == 'true');

      if (!unique) {
        if (this.promises.hasOwnProperty(condition.field.pluginId)) {
          return this.promises[condition.field.pluginId];
        }
      }
      let promise = new Promise((resolve, reject) => {
        let result = false;
        // todo: allow match by plugin only.
        if (typeof Drupal.smartContent.plugin.Field[condition.field.pluginId] !== 'undefined') {
          result = Drupal.smartContent.plugin.Field[condition.field.pluginId](condition);
        }
        else if (typeof Drupal.smartContent.plugin.Field[condition.field.pluginId.split(':')[0]] !== 'undefined') {
          result = Drupal.smartContent.plugin.Field[condition.field.pluginId.split(':')[0]](condition);
        }
        else {
          result = false;
        }
        resolve(result);

        // }, Math.floor(Math.random()*(1000-100+1)+100 ))
      });

      if (!unique) {
        this.promises[condition.field.pluginId] = promise;
      }
      return promise;
    },
  };


  Drupal.smartContent.SmartContentManager = {};
  // Loops through all segments and processes them.
  Drupal.smartContent.SmartContentManager.processSegments = function (segments) {
    Object.keys(segments).forEach(key => {
      Drupal.smartContent.processSegment(segments[key])
    });
  };

  Drupal.smartContent.SmartContentManager.processDecisions = function (decisions) {
    Object.keys(decisions).forEach(async (key) => {
      let decision = decisions[key];
      let reactions = decision.reactions;

      let winner;
      for (let i = 0; i < reactions.length; i++) {
        let result = await Promise.resolve(Drupal.smartContent.segmentManager.lookup(reactions[i]));
        if (result) {
          winner = reactions[i];
          break;
        }
      }

      if (winner) {
        // Winner found.
        Drupal.smartContent.processWinner(decision, winner);
      }
      else {
        // No winner found. Check if default is defined.
        if (decision.hasOwnProperty('default')) {
          Drupal.smartContent.processWinner(decision, decision.default, true);
        }
      }
    });
  };

  Drupal.smartContent.processWinner = function (decision, winner, isDefault = false) {
    let basePath = drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix;
    let url = basePath + 'ajax/smart_content/' + decision.storage + '/' + decision.token + '/' + winner;
    let ajaxObject = new Drupal.ajax({
      url: url,
      progress: false,
      async: true,
      success: function (response, status) {
        for (var i in response) {
          if (response.hasOwnProperty(i) && response[i].command && this.commands[response[i].command]) {
            this.commands[response[i].command](this, response[i], status);
          }
        }
      }
    });
    ajaxObject.options.type = "GET";
    ajaxObject.execute();

    // Broadcast the winner.
    let event = new CustomEvent('smart_content_decision', {
      detail: {
        winner: winner,
        default: isDefault,
        settings: drupalSettings.smartContent,
      },
    });
    window.dispatchEvent(event);
  };

  /**
   * Processes the segment.
   *
   * Registers the segment for processing in the segmentManager and returns a
   * promise.
   */
  Drupal.smartContent.processSegment = function (settings) {
    return Drupal.smartContent.segmentManager.register(settings)
  };

  /**
   * Processes the condition.
   *
   * Registers the condition's field for processing in the
   * conditionFieldManager. Then evaluates the field and returns a promise for
   * the result.
   */
  Drupal.smartContent.processCondition = function (settings) {
    let fieldPromise = Drupal.smartContent.conditionFieldManager.register(settings);
    // todo: Because this waits for all fields to be satisfied, we cannot early
    // return group processing.
    return Promise.resolve(fieldPromise).then(function (value) {
      let result = false;
      if (typeof Drupal.smartContent.plugin.ConditionType[settings.field.type] !== 'undefined') {
        result = Drupal.smartContent.plugin.ConditionType[settings.field.type](settings, value);
      }
      else if (typeof Drupal.smartContent.plugin.Condition[settings.field.pluginId] !== 'undefined') {
        result = Drupal.smartContent.plugin.Condition[settings.field.pluginId](settings, value);
      }
      let negate = settings.settings.hasOwnProperty('negate') && settings.settings.negate == true;
      return negate ? !result : result;
    })
  };

  // todo: Attempt to nest smart content decisions.
  Drupal.smartContent.init(drupalSettings.smartContent);

})(Drupal, drupalSettings);
