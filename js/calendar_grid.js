/*
    Copyright (c) 2007-2026 Ovensia
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
 * Planning du socle, rendu moderne (ploopi\calendar sous ploopi4) : déplacement d'un
 * événement (data-calendar-drop) par son en-tête, à la souris, au stylet ou au doigt,
 * vers une autre journée et une autre heure, à la demi-heure. La requête est celle du
 * rendu historique : calendar_event_date (AAAAMMJJ) et calendar_event_hour (12.5 =
 * 12 h 30), envoyée à l'URL de l'événement, réponse affichée dans sa zone.
 */

ploopi.calendargrid = (function() {

    var drag = null;

    var dayAt = function(x, y, ev) {
        ev.style.visibility = 'hidden';
        var under = document.elementFromPoint(x, y);
        ev.style.visibility = '';
        var day = under ? under.closest('.p4-cal-day[data-calendar-date]') : null;
        return day && day.closest('.p4-cal') === ev.closest('.p4-cal') ? day : null;
    };

    var mark = function(day) {
        if (drag.day && drag.day !== day) drag.day.classList.remove('is-drop-target');
        if (day) day.classList.add('is-drop-target');
        drag.day = day;
    };

    var end = function() {
        if (!drag) return null;
        var d = drag;
        drag = null;
        if (d.day) d.day.classList.remove('is-drop-target');
        d.ev.classList.remove('is-dragging');
        d.ev.style.transform = '';
        return d;
    };

    jQuery(document).on('pointerdown', '.p4-cal-ev.is-draggable .p4-cal-ev-head', function(e) {
        if (e.button !== 0 || jQuery(e.target).closest('a, button').length) return;
        var ev = this.closest('.p4-cal-ev');
        drag = { ev: ev, x: e.clientX, y: e.clientY, dy: e.clientY - ev.getBoundingClientRect().top, moved: false, day: null };
        if (this.setPointerCapture) this.setPointerCapture(e.pointerId);
        e.preventDefault();
    });

    jQuery(document).on('pointermove', function(e) {
        if (!drag) return;
        var dx = e.clientX - drag.x, dy = e.clientY - drag.y;
        if (!drag.moved && Math.abs(dx) + Math.abs(dy) < 5) return;
        drag.moved = true;
        drag.ev.classList.add('is-dragging');
        drag.ev.style.transform = 'translate(' + dx + 'px, ' + dy + 'px)';
        mark(dayAt(e.clientX, e.clientY, drag.ev));
    });

    jQuery(document).on('pointercancel', end);

    jQuery(document).on('pointerup', function(e) {
        if (!drag) return;
        var day = drag.moved ? dayAt(e.clientX, e.clientY, drag.ev) : null;
        var d = end();
        if (!day) return;

        var root = day.closest('.p4-cal');
        var begin = parseFloat(root.getAttribute('data-calendar-hour-begin')) || 0;
        var finish = parseFloat(root.getAttribute('data-calendar-hour-end')) || 24;
        var rect = day.getBoundingClientRect();

        // Heure du haut de l'événement déposé, à la demi-heure
        var hour = begin + (e.clientY - d.dy - rect.top) / rect.height * (finish - begin);
        hour = Math.min(finish - 0.5, Math.max(begin, Math.round(hour * 2) / 2));

        var target = d.ev.getAttribute('data-calendar-drop-target');
        if (!document.getElementById(target)) return;
        ploopi.xhr.todiv(d.ev.getAttribute('data-calendar-drop'), 'calendar_event_date=' + day.getAttribute('data-calendar-date') + '&calendar_event_hour=' + hour, target);
    });

    // Ouverture : la grille défile jusqu'à l'heure utile (data-calendar-scroll : premier
    // événement de la période, ou 8 h), sur la page puis dans tout contenu inséré
    var scroll = function(node) {
        jQuery(node).find('.p4-cal-days[data-calendar-scroll]').addBack('.p4-cal-days[data-calendar-scroll]').each(function() {
            var scroller = this.querySelector('.p4-cal-scroll'), hour = this.querySelector('.p4-cal-hour');
            if (!scroller || !hour) return;
            var begin = parseFloat(this.getAttribute('data-calendar-hour-begin')) || 0;
            scroller.scrollTop = Math.max(0, (parseFloat(this.getAttribute('data-calendar-scroll')) - begin) * hour.offsetHeight);
        });
    };

    jQuery(function() {
        if (ploopi.popup && ploopi.popup.addenhancer) ploopi.popup.addenhancer(scroll);
    });

    return { dayAt: dayAt, scroll: scroll };
})();
