/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * A class to parse color values
 * @author Stoyan Stefanov <sstoo@gmail.com>
 * @link   http://www.phpied.com/rgb-color-parser-in-javascript/
 * @license Use it if you like it
 */

function ploopi_rgbcolor(color_string)
{
    this.ok = false;

    // strip any leading #
    if (color_string.charAt(0) == '#') { // remove # if any
        color_string = color_string.substr(1,6);
    }

    color_string = color_string.replace(/ /g,'');
    color_string = color_string.toLowerCase();

    // before getting into regexps, try simple matches
    // and overwrite the input
    var simple_colors = {
        aliceblue: 'f0f8ff',
        antiquewhite: 'faebd7',
        aqua: '00ffff',
        aquamarine: '7fffd4',
        azure: 'f0ffff',
        beige: 'f5f5dc',
        bisque: 'ffe4c4',
        black: '000000',
        noir: '000000',
        blanchedalmond: 'ffebcd',
        blue: '0000ff',
        bleu: '0000ff',
        blueviolet: '8a2be2',
        brown: 'a52a2a',
        burlywood: 'deb887',
        cadetblue: '5f9ea0',
        chartreuse: '7fff00',
        chocolate: 'd2691e',
        coral: 'ff7f50',
        cornflowerblue: '6495ed',
        cornsilk: 'fff8dc',
        crimson: 'dc143c',
        cyan: '00ffff',
        darkblue: '00008b',
        darkcyan: '008b8b',
        darkgoldenrod: 'b8860b',
        darkgray: 'a9a9a9',
        darkgreen: '006400',
        darkkhaki: 'bdb76b',
        darkmagenta: '8b008b',
        darkolivegreen: '556b2f',
        darkorange: 'ff8c00',
        darkorchid: '9932cc',
        darkred: '8b0000',
        darksalmon: 'e9967a',
        darkseagreen: '8fbc8f',
        darkslateblue: '483d8b',
        darkslategray: '2f4f4f',
        darkturquoise: '00ced1',
        darkviolet: '9400d3',
        deeppink: 'ff1493',
        deepskyblue: '00bfff',
        dimgray: '696969',
        dodgerblue: '1e90ff',
        feldspar: 'd19275',
        firebrick: 'b22222',
        floralwhite: 'fffaf0',
        forestgreen: '228b22',
        fuchsia: 'ff00ff',
        gainsboro: 'dcdcdc',
        ghostwhite: 'f8f8ff',
        gold: 'ffd700',
        goldenrod: 'daa520',
        gray: '808080',
        gris: '808080',
        green: '008000',
        vert: '008000',
        greenyellow: 'adff2f',
        honeydew: 'f0fff0',
        hotpink: 'ff69b4',
        indianred : 'cd5c5c',
        indigo : '4b0082',
        ivory: 'fffff0',
        khaki: 'f0e68c',
        lavender: 'e6e6fa',
        lavenderblush: 'fff0f5',
        lawngreen: '7cfc00',
        lemonchiffon: 'fffacd',
        lightblue: 'add8e6',
        lightcoral: 'f08080',
        lightcyan: 'e0ffff',
        lightgoldenrodyellow: 'fafad2',
        lightgrey: 'd3d3d3',
        lightgreen: '90ee90',
        lightpink: 'ffb6c1',
        lightsalmon: 'ffa07a',
        lightseagreen: '20b2aa',
        lightskyblue: '87cefa',
        lightslateblue: '8470ff',
        lightslategray: '778899',
        lightsteelblue: 'b0c4de',
        lightyellow: 'ffffe0',
        lime: '00ff00',
        limegreen: '32cd32',
        linen: 'faf0e6',
        magenta: 'ff00ff',
        maroon: '800000',
        mediumaquamarine: '66cdaa',
        mediumblue: '0000cd',
        mediumorchid: 'ba55d3',
        mediumpurple: '9370d8',
        mediumseagreen: '3cb371',
        mediumslateblue: '7b68ee',
        mediumspringgreen: '00fa9a',
        mediumturquoise: '48d1cc',
        mediumvioletred: 'c71585',
        midnightblue: '191970',
        mintcream: 'f5fffa',
        mistyrose: 'ffe4e1',
        moccasin: 'ffe4b5',
        navajowhite: 'ffdead',
        navy: '000080',
        oldlace: 'fdf5e6',
        olive: '808000',
        olivedrab: '6b8e23',
        orange: 'ffa500',
        orangered: 'ff4500',
        orchid: 'da70d6',
        palegoldenrod: 'eee8aa',
        palegreen: '98fb98',
        paleturquoise: 'afeeee',
        palevioletred: 'd87093',
        papayawhip: 'ffefd5',
        peachpuff: 'ffdab9',
        peru: 'cd853f',
        pink: 'ffc0cb',
        rose: 'ffc0cb',
        plum: 'dda0dd',
        powderblue: 'b0e0e6',
        purple: '800080',
        red: 'ff0000',
        rouge: 'ff0000',
        rosybrown: 'bc8f8f',
        royalblue: '4169e1',
        saddlebrown: '8b4513',
        salmon: 'fa8072',
        sandybrown: 'f4a460',
        seagreen: '2e8b57',
        seashell: 'fff5ee',
        sienna: 'a0522d',
        silver: 'c0c0c0',
        skyblue: '87ceeb',
        slateblue: '6a5acd',
        slategray: '708090',
        snow: 'fffafa',
        springgreen: '00ff7f',
        steelblue: '4682b4',
        tan: 'd2b48c',
        teal: '008080',
        thistle: 'd8bfd8',
        tomato: 'ff6347',
        turquoise: '40e0d0',
        violet: 'ee82ee',
        violetred: 'd02090',
        wheat: 'f5deb3',
        white: 'ffffff',
        blanc: 'ffffff',
        whitesmoke: 'f5f5f5',
        yellow: 'ffff00',
        jaune: 'ffff00',
        yellowgreen: '9acd32'
    };
    for (var key in simple_colors) {
        if (color_string == key) {
            color_string = simple_colors[key];
        }
    }
    // emd of simple type-in colors

    // array of color definition objects
    var color_defs = [
        {
            re: /^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,
            example: ['rgb(123, 234, 45)', 'rgb(255,234,245)'],
            process: function (bits){
                return [
                    parseInt(bits[1]),
                    parseInt(bits[2]),
                    parseInt(bits[3])
                ];
            }
        },
        {
            re: /^(\w{2})(\w{2})(\w{2})$/,
            example: ['#00ff00', '336699'],
            process: function (bits){
                return [
                    parseInt(bits[1], 16),
                    parseInt(bits[2], 16),
                    parseInt(bits[3], 16)
                ];
            }
        },
        {
            re: /^(\w{1})(\w{1})(\w{1})$/,
            example: ['#fb0', 'f0f'],
            process: function (bits){
                return [
                    parseInt(bits[1] + bits[1], 16),
                    parseInt(bits[2] + bits[2], 16),
                    parseInt(bits[3] + bits[3], 16)
                ];
            }
        }
    ];

    // search through the definitions to find a match
    for (var i = 0; i < color_defs.length; i++) {
        var re = color_defs[i].re;
        var processor = color_defs[i].process;
        var bits = re.exec(color_string);
        if (bits) {
            channels = processor(bits);
            this.r = channels[0];
            this.g = channels[1];
            this.b = channels[2];
            this.ok = true;
        }

    }

    // validate/cleanup values
    this.r = (this.r < 0 || isNaN(this.r)) ? 0 : ((this.r > 255) ? 255 : this.r);
    this.g = (this.g < 0 || isNaN(this.g)) ? 0 : ((this.g > 255) ? 255 : this.g);
    this.b = (this.b < 0 || isNaN(this.b)) ? 0 : ((this.b > 255) ? 255 : this.b);

    // some getters
    this.toRGB = function () {
        return 'rgb(' + this.r + ', ' + this.g + ', ' + this.b + ')';
    }
    this.toHex = function () {
        var r = this.r.toString(16);
        var g = this.g.toString(16);
        var b = this.b.toString(16);
        if (r.length == 1) r = '0' + r;
        if (g.length == 1) g = '0' + g;
        if (b.length == 1) b = '0' + b;
        return '#' + r + g + b;
    }

    // help
    this.getHelpXML = function () {

        var examples = new Array();
        // add regexps
        for (var i = 0; i < color_defs.length; i++) {
            var example = color_defs[i].example;
            for (var j = 0; j < example.length; j++) {
                examples[examples.length] = example[j];
            }
        }
        // add type-in colors
        for (var sc in simple_colors) {
            examples[examples.length] = sc;
        }

        var xml = document.createElement('ul');
        xml.setAttribute('id', 'rgbcolor-examples');
        for (var i = 0; i < examples.length; i++) {
            try {
                var list_item = document.createElement('li');
                var list_color = new ploopi_rgbcolor(examples[i]);
                var example_div = document.createElement('div');
                example_div.style.cssText =
                        'margin: 3px; '
                        + 'border: 1px solid black; '
                        + 'background:' + list_color.toHex() + '; '
                        + 'color:' + list_color.toHex()
                ;
                example_div.appendChild(document.createTextNode('test'));
                var list_item_value = document.createTextNode(
                    ' ' + examples[i] + ' -> ' + list_color.toRGB() + ' -> ' + list_color.toHex()
                );
                list_item.appendChild(example_div);
                list_item.appendChild(list_item_value);
                xml.appendChild(list_item);

            } catch(e){}
        }
        return xml;

    }
}


function ploopi_validatefield(field_label, field_object, field_type)
{
    var ok = true;
    var i;
    var nbpoint = 0;
    var msg = new String();
    var reg = new RegExp("<FIELD_LABEL>","gi");

    if (field_object)
    {
        field_value = field_object.value;

        /* Vérifie qu'un élément de liste a été sélectionné */
        if (field_type == 'selected')
        {
            msg = lstmsg[9];
            ok = (field_object.selectedIndex > 0 && field_object.value != '');
        }

        /* Vérifie qu'une checkbox (ou bouton radio) à été cochée */
        if (field_type == 'checked')
        {
            msg = lstmsg[9];
            ok = false;
            for (c = 0; c < field_object.length; c++)
            {
                if (field_object[c].checked) ok = true;
            }
        }

        /* Vérifie que le champ contient une numéro de téléphone valide */
        if (field_type == 'phone' || field_type == 'emptyphone')
        {
            ok = (field_value.search(/^\+?(\([0-9 ]+\))?[0-9 ]+$/) != -1);
            if (field_type == 'emptyphone') ok = (ok || field_value.length == 0);
            if (!ok) msg = (field_type == 'phone' && field_value.length == 0) ? lstmsg[4] : lstmsg[11];
        }

        /* Vérifie que le champ contient une adresse email valide */
        if (field_type == 'email' || field_type == 'emptyemail')
        {
            ok = (field_value.search(/^[a-z0-9._-]+@[a-z0-9.-]{2,}[.][a-z]{2,4}$/) != -1);
            if (field_type == 'emptyemail') ok = (ok || field_value.length == 0);
            if (!ok) msg = (field_type == 'email' && field_value.length == 0) ? lstmsg[4] : lstmsg[0];
        }

        /* Vérifie que le champ contient une couleur valide */
        if (field_type == 'color')
        {
            var color = new ploopi_rgbcolor(field_value);
            if (!color.ok)
            {
                ok = false;
                msg = lstmsg[10];
            }
        }

        /* Vérifie que le champ contient une chaîne non vide (espace non compris) */
        if (field_type == 'string')
        {
            ok = (field_value.replace(/(^\s*)|(\s*$)/g,'').length > 0)
            if (!ok) msg = lstmsg[4];
        }

        /* Vérifie que le champ contient une valeur entière ou vide */
        if (field_type == 'int' || field_type == 'emptyint')
        {
            ok = (field_value.search(/^(\-?[0-9]+)$/) != -1);
            if (field_type == 'emptyint') ok = (ok || field_value.length == 0);
            if (!ok) msg = (field_type == 'int' && field_value.length == 0) ? lstmsg[4] : lstmsg[5];
        }

        /* Vérifie que le champ contient une valeur réelle ou vide */
        if (field_type == 'float' || field_type == 'emptyfloat')
        {
            ok = (field_value.search(/^(\-?((([0-9]+(\.)?)|([0-9]*\.[0-9]+))))$/) != -1);
            if (field_type == 'emptyfloat') ok = (ok || field_value.length == 0);
            if (!ok) msg = (field_type == 'float' && field_value.length == 0) ? lstmsg[4] : lstmsg[6];
        }

        /* Vérifie que le champ contient une date valide ou vide */
        if (field_type == 'date' || field_type == 'emptydate')
        {
            ok = (field_value.search(/^([0-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/) != -1);
            if (ok && field_value.length > 0)
            {
                var date_split = field_value.split("/");
                for (i=0;i<=2;i++) date_split[i] = parseInt(date_split[i],10);
                var datetotest = new Date(date_split[2], date_split[1]-1, date_split[0]);
                ok = ( datetotest.getDate() == date_split[0] && datetotest.getMonth() == date_split[1]-1 && datetotest.getFullYear() == date_split[2] );
            }

            if (field_type == 'emptydate') ok = (ok || field_value.length == 0);
            if (!ok) msg = (field_type == 'date' && field_value.length == 0) ? lstmsg[4] : lstmsg[7];
        }

        /* Vérifie que le champ contient une heure valide ou vide */
        if (field_type == 'time' || field_type == 'emptytime')
        {
            ok = (field_value.search(/^(0[0-9]|1[0-9]|2[0-4]):[0-5][0-9](:[0-5][0-9])?$/) != -1);
            if (field_type == 'emptytime') ok = (ok || field_value.length == 0);
            if (!ok) msg = (field_type == 'time' && field_value.length == 0) ? lstmsg[4] : lstmsg[8];
        }
    }
    else ok = false;

    if (!ok)
    {
        alert(msg.replace(reg,field_label));
        if (field_type != 'checked')
        {
            field_object.style.background = error_bgcolor;
            field_object.focus();
        }
    }

    return (ok);
}