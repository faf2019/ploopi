/**
 * NAVIGATION CALENDRIER
 */

function planning_nextmonth()
{
    if (jQuery('#planning_month')[0].value < 12) jQuery('#planning_month')[0].selectedIndex++;
    else {jQuery('#planning_month')[0].selectedIndex = 0; jQuery('#planning_year')[0].selectedIndex++;}

    jQuery('#planning_form_view')[0].onsubmit();
}

function planning_prevmonth()
{
    if (jQuery('#planning_month')[0].value > 1) jQuery('#planning_month')[0].selectedIndex--;
    else {jQuery('#planning_month')[0].selectedIndex = 11; jQuery('#planning_year')[0].selectedIndex--;}

    jQuery('#planning_form_view')[0].onsubmit();
}

function planning_nextweek()
{
    if (jQuery('#planning_week')[0].selectedIndex < jQuery('#planning_week')[0].length - 1) jQuery('#planning_week')[0].selectedIndex++;
    else {jQuery('#planning_week')[0].selectedIndex = 0; jQuery('#planning_year')[0].selectedIndex++;}

    jQuery('#planning_form_view')[0].onsubmit();
}

function planning_prevweek()
{
    if (jQuery('#planning_week')[0].selectedIndex > 0) jQuery('#planning_week')[0].selectedIndex--;
    else {jQuery('#planning_week_previousyear')[0].value = '1'; jQuery('#planning_year')[0].selectedIndex--;}

    jQuery('#planning_form_view')[0].onsubmit();
}

function planning_nextday()
{
    if (jQuery('#planning_day')[0].selectedIndex < jQuery('#planning_day')[0].length - 1)
    {
        jQuery('#planning_day')[0].selectedIndex++;
        jQuery('#planning_form_view')[0].onsubmit();
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
        jQuery('#planning_form_view')[0].onsubmit();
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