<div id="cart" style="padding:15px">
    <h1>Warenkorb</h1>
    <?php if ($message) : ?>
        <div class="tooltip" style="position: relative;margin:1em 0">
            <?php
            switch ($message) {
                case 'error': echo 'Filter Konnte nicht gelöscht werden';
                    break;
                case 'success': echo 'Filter Erfolgreich gelöscht';
                    break;
                default: echo 'Ubekannter Fehler';
                    break;
            }
            ?>
        </div>
    <?php endif; ?>
    <?php if (count($filters) > 0): ?>


        <table class="result" style="margin:10px 0px">

            <?php foreach ($projects as $projectID => $project): ?>


                <tr>
                    <td width="10%">ZA <?= $project['za'] ?></td>
                    <td width="15%" class="even"><?= $project['theme'] ?></td>
                    <td width="55%"><?= $project['name'] ?></td>
                    <td style="display:none" width="23%" class="even found show"><span class="id" style="display:none"><?= $projectID ?></span>Tabellen anzeigen</td>
                    <td  width="23%" class="even found hide"><span class="id" style="display:none"><?= $projectID ?></span>Tabellen schließen</td>

                </tr>

                <tr class="empty tables <?= $projectID ?>" >

                    <td class="nopadding tabes border-all"  colspan="4" >
                        <?php foreach ($tables[$projectID] as $tableID => $tableName) : ?>
                            <div style="padding:5px">
                                <?php foreach ($filters[$projectID][$tableID] as $filter => $values) : ?>
                                    <div class="left" style="width:10%;text-align: center"><?= Form::checkbox('selected[]', $tableID . '/' . $filter); ?></div>
                                    <div class="normal left"><h4><?= $tableName ?></h4></div>
                                    <div class="right">
                                        <span class="timelines"><?= HTML::anchor('table/details/' . $tableID . '/' . $filter . '#tabelle', __(':timelines Zeitreihen', array(':timelines' => $values['timelines']))) ?></span>
                                        <span class="delete"> <?= HTML::anchor('cart/delete/' . $tableID . '/' . $filter, '') ?></span>
                                    </div>
                                    <div class="clear"></div>

                                    <?php foreach ($values['text'] as $value) : ?>

                                        <?php if (!stristr($value, __('All'))) : ?>
                                            <p class="filter"><?= $value ?><p>
                                            <?php endif ?>
                                        <?php endforeach; ?>

                                    <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </td>

                </tr>



            <?php endforeach; ?>
        </table>
        <div class="clear"></div>

        <div style="position: relative;"> 
            <?= Form::open('cart/delete_selected', array('class' => 'selected')) ?>
            <?= Form::submit('delete', __('Delete'), array('class' => 'button', 'style' => 'width:150px;height:40px;max-height:none')) ?>
            <?= Form::close() ?>
            <?= Form::open('cart/download_selected', array('class' => 'selected', 'id' => 'download')) ?>
            <div class="download" style="position: relative;width:500px">
                <div class="button" style="width:150px;position: relative;text-align: center">Download</div>
                <div class="buttons" >
                    <a class="button" id="xls" href="#" >.XLS</a>
                    <a class="button" id="xlsx" href="#">.XLSX</a>
                    <a class="button" id="csv" href="#">.CSV</a>
                </div>
                <div class="clear"></div>
            </div>
            <div class="overlay transparent" style="display:none"></div>
            <div class="dialog tooltip" style="display:none">
                <p class="normal">
                    Die Bedingungen für die Nutzung unseres Download-Datenservices sind in der aktuellen Benutzungsordnung des GESIS - Datenarchivs festgelegt. Auf der GESIS - Homepage können Sie sich unter "Dienstleistungen / Recherche & Datenzugang / Datenarchiv Service / Benutzungsordnung" über die aktuell geltenden Bedingungen zur Nutzung von Studien und Daten informieren. Für gewerbliche Zwecke sind die Vervielfältigungen und Verbreitung exportierter Daten aus Studien des GESIS - Datenarchivs grundsätzlich nicht gestattet.
                </p>
                <p class="normal">
                    Die zugangsklasse A dieser Studie erfordert die Angabe eines Verwendungszweckes. Mit der Angabe des Verwendungszwekcs und dem anschließenden Download stimmen Sie den Nutzungsbediungen zu.
                </p>

                <?= Form::select('uses', $options) ?>
                <?= Form::input('custom') ?>
                <?= Form::submit('download', __('Start Download'), array('class' => 'button')) ?>

            </div>
            <?= Form::hidden('format', null) ?>
            <?= Form::close() ?>
        </div>

        <div class="clear"></div>
    <?php else : ?>
        <h3>Ihr Warenkorb ist leer</h3>
    <?php endif; ?>


</div>