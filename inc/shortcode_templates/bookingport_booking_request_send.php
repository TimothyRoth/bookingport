<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table); ?>

<div class="wrapper">
    <div class="entry-content">
        <p>Vielen Dank für Ihre Buchungsanfrage!</p>
        <p><span data-contrast="auto">Parallel erhalten Sie eine Bestätigung Ihrer Anfrage per E-Mail. </span></p>
        <p><span data-contrast="auto">Nachdem Ihre Anfrage geprüft wurde erhalten Sie eine weitere E-Mail mit einem konkreten Buchungsangebot. Mit diesem Angebot erhalten Sie einen Link, um die Buchung bezahlen und somit abschließen zu können.</span><span
                    data-ccp-props="{}"> </span></p>
        <p>Hinweis: bitte prüfen Sie auch Ihren Spamordner</p>
        <p><span class="TextRun SCXW93358721 BCX2" lang="DE-DE" xml:lang="DE-DE" data-contrast="auto"><span
                        class="NormalTextRun SCXW93358721 BCX2">Alternativ können Sie das </span><span
                        class="NormalTextRun ContextualSpellingAndGrammarErrorV2Themed SCXW93358721 BCX2">Buchungsangebot</span><span
                        class="NormalTextRun SCXW93358721 BCX2"> sobald es vorliegt auch in Ihrem Account einsehen unter „<a
                            href="tos"><strong>Meine Anfragen.</strong></a></span><span
                        class="NormalTextRun SCXW93358721 BCX2">“</span><span
                        class="NormalTextRun SCXW93358721 BCX2">  </span></span></p>
        <ul>
            <li><a href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard] ?>">Zur Übersicht</a>
            </li>


            <li><a href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_my_bookings] ?>">Meine
                    Standbuchungen</a></li>


            <li><a href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_customer_requests] ?>">Meine
                    Anfragen</a></li>
        </ul>
    </div>
</div>
