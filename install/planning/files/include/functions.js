/**
 * NAVIGATION CALENDRIER
 */

// Période affichée : formulaire de la vue envoyé en XHR
function planning_view_submit()
{
    ploopi.xhr.submit(jQuery('#planning_form_view')[0], 'planning_main');
}

function planning_nextmonth()
{
    if (jQuery('#planning_month')[0].value < 12) jQuery('#planning_month')[0].selectedIndex++;
    else {jQuery('#planning_month')[0].selectedIndex = 0; jQuery('#planning_year')[0].selectedIndex++;}

    planning_view_submit();
}

function planning_prevmonth()
{
    if (jQuery('#planning_month')[0].value > 1) jQuery('#planning_month')[0].selectedIndex--;
    else {jQuery('#planning_month')[0].selectedIndex = 11; jQuery('#planning_year')[0].selectedIndex--;}

    planning_view_submit();
}

function planning_nextweek()
{
    if (jQuery('#planning_week')[0].selectedIndex < jQuery('#planning_week')[0].length - 1) jQuery('#planning_week')[0].selectedIndex++;
    else {jQuery('#planning_week')[0].selectedIndex = 0; jQuery('#planning_year')[0].selectedIndex++;}

    planning_view_submit();
}

function planning_prevweek()
{
    if (jQuery('#planning_week')[0].selectedIndex > 0) jQuery('#planning_week')[0].selectedIndex--;
    else {jQuery('#planning_week_previousyear')[0].value = '1'; jQuery('#planning_year')[0].selectedIndex--;}

    planning_view_submit();
}

function planning_nextday()
{
    if (jQuery('#planning_day')[0].selectedIndex < jQuery('#planning_day')[0].length - 1)
    {
        jQuery('#planning_day')[0].selectedIndex++;
        planning_view_submit();
    }
    else
    {
        jQuery('#planning_day')[0].selectedIndex = 0;
        planning_nextmonth();
    }
}

function planning_prevday()
{
    if (jQuery('#planning_day')[0].selectedIndex > 0)
    {
        jQuery('#planning_day')[0].selectedIndex--;
        planning_view_submit();
    }
    else
    {
        jQuery('#planning_week_previousmonth')[0].value = '1'
        planning_prevmonth();
    }
}

/**
 * AJOUT D'UN EVENEMENT
 */
function planning_event_validate(form)
{
    if (ploopi.validatefield('Objet',form.planning_event_object, 'string'))
    if (ploopi.validatefield('Date de début',form._planning_event_timestp_begin_d, 'date'))
    if (!form._planning_event_timestp_end_d || ploopi.validatefield('Date de fin',form._planning_event_timestp_end_d, 'date'))
    {
        ploopi.popup.hide('popup_planning_event');
        return true;
    }

    return false;
}

/**
 * Comportements par attributs, sans gestionnaire en ligne (CSP stricte) : actions
 * appelées par data-ploopi-action (js/actions.js du socle), formulaires envoyés en XHR
 */
ploopi.actions = ploopi.actions || {};

jQuery.each(['planning_prevmonth', 'planning_nextmonth', 'planning_prevweek', 'planning_nextweek', 'planning_prevday', 'planning_nextday'], function(i, name) {
    ploopi.actions[name] = function(el, event) { window[name](); };
});

// Impression du planning, dans une fenêtre
ploopi.actions.planning_openwin = function(el, event, url, width, height) {
    event.preventDefault();
    ploopi.openwin(url, width, height);
};

// Taille du planning
ploopi.actions.planning_size = function(el, event, url) {
    ploopi.xhr.todiv(url, 'planning_size='+encodeURIComponent(el.value), 'planning_main');
};

// Date de début recopiée dans la date de fin (nouvel événement)
ploopi.actions.planning_copyvalue = function(el, event, target) {
    var dest = document.getElementById(target);
    if (dest) dest.value = el.value;
};

// Suppression d'un événement (bouton de la fenêtre, croix du calendrier)
ploopi.actions.planning_event_delete = function(el, event, query) {
    event.preventDefault();
    if (!confirm('Êtes vous certain de vouloir supprimer cet événement ?')) return;
    ploopi.xhr.todiv('admin-light.php', query, 'planning_main');
    ploopi.popup.hide('popup_planning_event');
};

// Formulaires envoyés en XHR vers la zone data-planning-submit, contrôlés au besoin
// (data-planning-validate : nouvel événement)
jQuery(document).on('submit', 'form[data-planning-submit]', function(e) {
    e.preventDefault();
    ploopi.xhr.submit(this, this.getAttribute('data-planning-submit'), this.hasAttribute('data-planning-validate') ? planning_event_validate : null);
});
