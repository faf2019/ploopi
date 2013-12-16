/* Simple password strength checker for prototype.
* (c) 2010 Bermi Ferrer
*
* Inspired by http://plugins.jquery.com/project/pstrength
*
* Distributable under the terms of an MIT-style license.
* For details, see the git site:
* http://github.com/bermi/protopass
*
*--------------------------------------------------------------------------*/

var Protopass = Class.create({
    initialize : function(item, hook, options) {
        this.item = $(item);
        this.hook = $(hook);
        this.field_name = this.item.id;
        this.options = {
            messages: ["Non s�r!", "Trop court", "Tr�s faible", "Faible", "Moyen", "Fort", "Tr�s fort"],
            colors: ["#f00", "#999", "#f00", "#c06", "#f60", "#3c0", "#2c0"],
            scores: [10, 15, 30, 40],
            common: ["password", "123456", "123", "1234", "mypass", "pass", "letmein", "azerty", "qsdfgh", "wxcvbn"],
            minchar: 8
        };
        Object.extend(this.options, options || { });
        this.hook.insert({'top': "<div class=\"password-strength-info\" id=\""+this.field_name+"_text\"></div>"});
        this.hook.insert({'top': "<div class=\"password-strength-bar\" id=\""+this.field_name+"_bar\" style=\"margin:5px 0; width: 0px;\"></div>"});

        this.bar = $(this.field_name + "_bar");
        this.feedback_text = $(this.field_name + "_text");

        this.item.observe('keyup', function () {
            this.checkUserPasswordStrength();
        }.bind(this));
    },

    checkUserPasswordStrength: function (field_name) {
        var options = this.options;
        var value = this.item.value;
        var field_name = this.field_name;

        strength = this.getPasswordScore(value, options);

        if (strength == -200) {
            this.displayPasswordStrengthFeedback(0, 0);
        } else {
            if (strength < 0 && strength > -199) {
                this.displayPasswordStrengthFeedback(1, 10);
            } else {
                if (strength <= options.scores[0]) {
                    this.displayPasswordStrengthFeedback(2, 10);
                } else {
                    if (strength > options.scores[0] && strength <= options.scores[1]) {
                        this.displayPasswordStrengthFeedback(3, 25);
                    } else if (strength > options.scores[1] && strength <= options.scores[2]) {
                        this.displayPasswordStrengthFeedback(4, 55);
                    } else if (strength > options.scores[2] && strength <= options.scores[3]) {
                        this.displayPasswordStrengthFeedback(5, 80);
                    } else {
                        this.displayPasswordStrengthFeedback(6, 98);
                    }
                }
            }
        }
    },

    displayPasswordStrengthFeedback: function(setting_index, percent_rate){
        this.feedback_text.innerHTML = "<span style='color: " + this.options.colors[setting_index] + ";'>" + this.options.messages[setting_index] + "</span>";
        this.bar.setStyle('width:'+percent_rate+'%;background-color:'+this.options.colors[setting_index]);
        this.bar.setStyle('height:8px');
    },

    getPasswordScore: function (value, options) {
        var strength = 0;
        if (value.length < options.minchar) {
            strength = (strength - 100);
        } else {
            if (value.length >= options.minchar && value.length <= (options.minchar + 2)) {
                strength = (strength + 6);
            } else {
                if (value.length >= (options.minchar + 3) && value.length <= (options.minchar + 4)) {
                    strength = (strength + 12);
                } else {
                    if (value.length >= (options.minchar + 5)) {
                        strength = (strength + 18);
                    }
                }
            }
        }
        if (value.match(/[a-z]/)) {
            strength = (strength + 1);
        }
        if (value.match(/[A-Z]/)) {
            strength = (strength + 5);
        }
        if (value.match(/\d+/)) {
            strength = (strength + 5);
        }
        if (value.match(/(.*[0-9].*[0-9].*[0-9])/)) {
            strength = (strength + 7);
        }
        if (value.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)) {
            strength = (strength + 5);
        }
        if (value.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)) {
            strength = (strength + 7);
        }
        if (value.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
            strength = (strength + 2);
        }
        if (value.match(/([a-zA-Z])/) && value.match(/([0-9])/)) {
            strength = (strength + 3);
        }
        if (value.match(/([a-zA-Z0-9].*[!,@,#,$,%,^,&,*,?,_,~])|([!,@,#,$,%,^,&,*,?,_,~].*[a-zA-Z0-9])/)) {
            strength = (strength + 3);
        }
        for (var i = 0; i < options.common.length; i++) {
            if (value.toLowerCase() == options.common[i]) {
                strength = -200
            }
        }
        return strength;
    }
});
