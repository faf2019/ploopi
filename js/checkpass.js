/* Simple password strength checker (c) 2010 Bermi Ferrer
*
* Inspired by http://plugins.jquery.com/project/pstrength
* Modified by OVENSIA (back to jQuery !)
*
* Distributable under the terms of an MIT-style license.
* For details, see the git site:
* http://github.com/bermi/protopass
*
*--------------------------------------------------------------------------*/

ploopi.checkpass = function(item, hook, options) {

    // Permet de gérer le conflit this
    var that = this;

    that.initialize = function(item, hook, options) {
        that.item = jQuery('#' + item).eq(0);
        that.hook = jQuery('#' + hook).eq(0);
        that.field_name = that.item.id;
        that.options = {
            messages: ["Non sûr!", "Trop court", "Très faible", "Faible", "Moyen", "Fort", "Très fort"],
            colors: ["#f00", "#999", "#f00", "#c06", "#f60", "#3c0", "#2c0"],
            scores: [10, 15, 30, 40],
            common: ["password", "123456", "123", "1234", "mypass", "pass", "letmein", "azerty", "qsdfgh", "wxcvbn"],
            minchar: 8
        };
        jQuery.extend(that.options, options || { });
        that.hook.prepend("<div class=\"password-strength-info\" id=\""+that.field_name+"_text\"></div>");
        that.hook.prepend("<div class=\"password-strength-bar\" id=\""+that.field_name+"_bar\" style=\"margin:5px 0; width: 0px;\"></div>");

        that.bar = jQuery('#' + that.field_name + "_bar").eq(0);
        that.feedback_text = jQuery('#' + that.field_name + "_text").eq(0);

        that.item.on('keyup', function() {
            that.checkUserPasswordStrength();
        });
    };

    that.checkUserPasswordStrength = function (field_name) {
        var options = that.options;
        var value = that.item[0].value;
        var field_name = that.field_name;

        strength = that.getPasswordScore(value, options);

        if (strength == -200) {
            that.displayPasswordStrengthFeedback(0, 0);
        } else {
            if (strength < 0 && strength > -199) {
                that.displayPasswordStrengthFeedback(1, 10);
            } else {
                if (strength <= options.scores[0]) {
                    that.displayPasswordStrengthFeedback(2, 10);
                } else {
                    if (strength > options.scores[0] && strength <= options.scores[1]) {
                        that.displayPasswordStrengthFeedback(3, 25);
                    } else if (strength > options.scores[1] && strength <= options.scores[2]) {
                        that.displayPasswordStrengthFeedback(4, 55);
                    } else if (strength > options.scores[2] && strength <= options.scores[3]) {
                        that.displayPasswordStrengthFeedback(5, 80);
                    } else {
                        that.displayPasswordStrengthFeedback(6, 98);
                    }
                }
            }
        }
    };

    that.displayPasswordStrengthFeedback = function(setting_index, percent_rate) {
        that.feedback_text[0].innerHTML = "<span style='color: " + that.options.colors[setting_index] + ";'>" + that.options.messages[setting_index] + "</span>";

        that.bar.css('width', percent_rate+'%');
        that.bar.css('background-color', that.options.colors[setting_index]);
        that.bar.css('height', '8px');
    };

    that.getPasswordScore = function (value, options) {
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
    };


    that.initialize(item, hook, options);
};
