(function (Drupal, drupalSettings) {

  drupalSettings.smartContent.tests = drupalSettings.smartContent.tests || {};
  Drupal.smartContentAB = Drupal.smartContentAB || {};

  Drupal.smartContentAB.testManager = {
    valuesLoaded: false,
    values: {},
    evaluate: function (id) {
      if(!this.valuesLoaded) {
        if (!Drupal.smartContent.storage.isExpired('smart_content_a_b')) {
          this.values =  Drupal.smartContent.storage.getValue('smart_content_a_b');
        }
        this.valuesLoaded = true;
      }
      if (!this.hasValue(id)) {
        if(this.hasSettings(id)) {
          this.setRandomValue(id, this.getSettings(id));
        }
        else {
          this.setValue(id, 'a');
        }
      }
      return this.getValue(id);
    },
    getValue: function (id) {
      return this.values[id];
    },

    hasValue: function (id) {
      return this.values.hasOwnProperty(id);
    },
    setValue: function (id, value) {
      this.values[id] = value;
      Drupal.smartContent.storage.setValue('smart_content_a_b', this.values, -1);
    },
    setRandomValue: function (id, count) {
      min = 0;
      max = count - 1;
      let randomNumber = Math.floor(Math.random() * (max - min + 1)) + min;
      this.setValue(id, this.getLetterByIndex(randomNumber));
      return this;
    },
    hasSettings: function (id) {
      return drupalSettings.smartContent.tests.hasOwnProperty(id);
    },
    getSettings: function (id) {
      return drupalSettings.smartContent.tests[id];
    },
    setSettings: function (id, value) {
      drupalSettings.smartContent.tests[id] = value;
    },
    getLetterByIndex: function (num) {
      num = num + 1;
      var str = "";

      let multiples = Math.ceil(num / 26);
      let charAtCode = num - ((multiples - 1) * 26)

      for (let i = 0; i < multiples; i++)
        str += String.fromCharCode(charAtCode + 64);

      return str.toLowerCase();
    }
  }

  Drupal.smartContent = Drupal.smartContent || {};
  Drupal.smartContent.plugin = Drupal.smartContent.plugin || {};
  Drupal.smartContent.plugin.Field = Drupal.smartContent.plugin.Field || {};
  Drupal.smartContent.plugin.Field['smart_content_a_b_value'] = function (condition) {
    return Drupal.smartContentAB.testManager.evaluate(condition.settings.entity_id);
  }

  Drupal.smartContent.plugin.Field['smart_content_a_b'] = function (condition) {
    if(condition.field.settings.hasOwnProperty('test')) {
      let entity_id = condition.field.settings.test.id;
      let count = condition.field.settings.test.count;
      if(!Drupal.smartContentAB.testManager.hasValue(entity_id) && !Drupal.smartContentAB.testManager.hasSettings(entity_id)) {
        Drupal.smartContentAB.testManager.setSettings(entity_id, count);
      }
      let value = Drupal.smartContentAB.testManager.evaluate(entity_id);
      return value;
    }
    return 'a';
  }

  Drupal.smartContent.plugin.Condition = Drupal.smartContent.plugin.Condition || {};
  Drupal.smartContent.plugin.Condition['smart_content_a_b_value'] = function (condition, value) {
    return condition.settings.letter == value;
  }

})(Drupal, drupalSettings);
