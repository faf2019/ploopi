<div class="system_trombi">
    <h1>Rechercher un utilisateur :</h1>
    <!-- Recherche Trombi -->
    <form action="{SYSTEM_TROMBI_FORMACTION}" method="post">
        <p class="ploopi_va">
            <label for="system_lastname">Nom:</label>
            <input type="text" name="system_lastname" id="system_lastname" class="text" style="width:100px;" tabindex="1010" value="{SYSTEM_TROMBI_LASTNAME}" />
            <label for="system_firstname">Prénom:</label>
            <input type="text" name="system_firstname" id="system_firstname" class="text" style="width:100px;" tabindex="1020" value="{SYSTEM_TROMBI_FIRSTNAME}" />
            <label for="system_service">Espace:</label>
            <select class="select" name="system_workspace" id="system_workspace" style="width:160px;" tabindex="1030">
                <option value="0"></option>
                <!-- BEGIN system_trombi_workspace -->
                    <option value="{system_trombi_workspace.ID}" {system_trombi_workspace.SELECTED}>{system_trombi_workspace.GAP}{system_trombi_workspace.LABEL}</option>
                <!-- END system_trombi_workspace -->
            </select>
            <!-- label for="system_service">Service:</label>
            <input type="text" name="system_service" id="system_service" class="text" style="width:90px;" tabindex="1040" /-->
            <label for="system_phone">Téléphone:</label>
            <input type="text" name="system_phone" id="system_phone" class="text" style="width:90px;" tabindex="1050" value="{SYSTEM_TROMBI_PHONE}" />
            <input type="submit" value="Rechercher" class="button" tabindex="1100" />
        </p>
    </form>

    <!-- BEGIN system_trombi_switch_result -->
    <div class="system_trombi_result">
    <h1>Résultat de la recherche :</h1>
        <!-- BEGIN switch_index -->
            <div class="system_trombi_index">
            <!-- BEGIN index -->
                <a class="{system_trombi_switch_result.switch_index.index.SELECTED}" title="{system_trombi_switch_result.switch_index.index.COUNT} utilisateur(s)" href="{system_trombi_switch_result.switch_index.index.URL}">{system_trombi_switch_result.switch_index.index.LETTER}</a>
            <!-- END index -->
            </div>
        <!-- END switch_index -->
        <table cellspacing="0" cellpadding="0" border="1">
            <tr class="system_trombi_title">
                <th>Nom</th>
                <th>Prénom</th>
                <th>Espace</th>
                <th>Fonction</th>
                <th>Téléphone</th>
            </tr>
            <!-- BEGIN user -->
                <tr class="system_trombi_user" onmouseover="javascript:this.style.backgroundColor='#e0e0ff';" onmouseout="javascript:this.style.backgroundColor='#ffffff';" onclick="javascript:ploopi_showpopup($('system_trombi_form{system_trombi_switch_result.user.ID}').innerHTML, 500, event, false, 'system_trombi_popup');" title="Ouvrir la fiche détaillée de {system_trombi_switch_result.user.LASTNAME} {system_trombi_switch_result.user.FIRSTNAME}">
                    <td>{system_trombi_switch_result.user.LASTNAME}</td>
                    <td>{system_trombi_switch_result.user.FIRSTNAME}</td>
                    <td>{system_trombi_switch_result.user.WORKSPACES}</td>
                    <td>{system_trombi_switch_result.user.FUNCTION}</td>
                    <td>{system_trombi_switch_result.user.PHONE}</td>
                </tr>
            <!-- END  user -->
        </table>
        <!-- BEGIN switch_message -->
           <p style="padding:4px 0;"><em>{system_trombi_switch_result.switch_message.CONTENT}</em></p>
        <!-- END  switch_message -->
    </div>
    <!-- END system_trombi_switch_result -->
</div>

<!-- BEGIN system_trombi_switch_result -->
    <!-- BEGIN user -->
        <div id="system_trombi_form{system_trombi_switch_result.user.ID}" style="display:none;">
            <div class="system_trombi_form">
                <h1>
                    <a href="javascript:void(0);" onclick="javascript:ploopi_hidepopup('system_trombi_popup'); return false;"  style="display:block;float:right">Fermer</a>
                    {system_trombi_switch_result.user.LASTNAME} {system_trombi_switch_result.user.FIRSTNAME}
                </h1>
                <div>
                    <div style="float:left;width:110px;">
                        <img title="Photo de {system_trombi_switch_result.user.LASTNAME} {system_trombi_switch_result.user.FIRSTNAME}" src="{system_trombi_switch_result.user.PHOTOPATH}" style="border:1px solid #404040;display:block;margin:5px auto;" />
                    </div>

                    <div style="margin-left:110px;border-left:1px solid #a0a0a0;">
                        <div class="ploopi_form" style="padding:4px;">
                            <h2>Information professionnelles</h2>
                            <p>
                                <label style="font-weight:bold;">Service:</label>
                                <span>{system_trombi_switch_result.user.SERVICE}</span>
                            </p>
                            <p>
                                <label style="font-weight:bold;">Fonction:</label>
                                <span>{system_trombi_switch_result.user.FUNCTION}</span>
                            </p>
                            <p>
                                <label style="font-weight:bold;">Grade/Niveau:</label>
                                <span>{system_trombi_switch_result.user.RANK}</span>
                            </p>
                            <p>
                                <label style="font-weight:bold;">Poste:</label>
                                <span>{system_trombi_switch_result.user.NUMBER}</span>
                            </p>
                            <p>
                                <label style="font-weight:bold;">Téléphone:</label>
                                <span>{system_trombi_switch_result.user.PHONE}</span>
                            </p>
                            <p>
                                <label style="font-weight:bold;">Mobile:</label>
                                <span>{system_trombi_switch_result.user.MOBILE}</span>
                            </p>
                            <p>
                                <label style="font-weight:bold;">Télécopie:</label>
                                <span>{system_trombi_switch_result.user.FAX}</span>
                            </p>
                            <p>
                                <label style="font-weight:bold;">Courriel:</label>
                                <span><a href="mailto:{system_trombi_switch_result.user.EMAIL}">{system_trombi_switch_result.user.EMAIL}</a></span>
                            </p>

                            <h2>Lieu de travail</h2>
                            <p>
                                <label style="font-weight:bold;">Bâtiment:</label>
                                <span>{system_trombi_switch_result.user.BUILDING}</span>
                            </p>
                            <p>
                                <label style="font-weight:bold;">Etage:</label>
                                <span>{system_trombi_switch_result.user.FLOOR}</span>
                            </p>
                            <p>
                                <label style="font-weight:bold;">Bureau:</label>
                                <span>{system_trombi_switch_result.user.OFFICE}</span>
                            </p>
                            <p>
                                <label style="font-weight:bold;">Adresse:</label>
                                <span>{system_trombi_switch_result.user.ADDRESS}</span>
                            </p>
                            <h2>Espaces de travail</h2>
                            <div style="padding:4px;">
                                {system_trombi_switch_result.user.WORKSPACES}
                            </div>
                            <h2>Groupes</h2>
                            <div style="padding:4px;">
                                {system_trombi_switch_result.user.GROUPS}
                            </div>
                            <h2>Attributions/Rôles</h2>
                            <div style="padding:4px;">
                                {system_trombi_switch_result.user.ROLES}
                            </div>

                            <!-- BEGIN switch_files -->
                            <h2>Documents</h2>
                            <div style="padding:4px;">
                                <!-- BEGIN file -->
                                <div>{system_trombi_switch_result.user.switch_files.file.PATH} &raquo; <a title="Télécharger le fichier" href="{system_trombi_switch_result.user.switch_files.file.URL}">{system_trombi_switch_result.user.switch_files.file.FILENAME}</a></div>
                                <!-- END file -->
                            </div>
                            <!-- END switch_files -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    <!-- END  user -->
<!-- END system_trombi_switch_result -->
