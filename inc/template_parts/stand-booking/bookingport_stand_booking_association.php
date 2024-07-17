<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table); ?>

<div class="form">
    <p>
        <label>Art des Vereins</label>
        <input name="association_type" class="prefilled-input" type="text" placeholder="Gastronomie">
    </p>

    <p>
        <label>Name des Vereins*</label>
        <input name="association_name" type="text" required>
    </p>

    <p>
        <label>Sortiment</label>
        <input name="association_sortiment" type="text">
    </p>

    <div class="stand-selection">
        <h3>Standauswahl</h3>
        <div class="stand-size-selection-wrapper" id="stand-size-selection-wrapper-verein">
            <div class="radio-selection">
                <p>
                    <label>Benötigte Standfläche*</label>
                </p>
                <p>
                    <input type="radio" id="equal" name="space-required" value="equal" required>
                    <label for="equal">Eine Standeinheit mit 3 m</label>
                </p>
                <p>
                    <input type="radio" id="more" name="space-required" value="more" required>
                    <label for="more">Ich brauche mehr als 3 m</label>
                </p>
            </div>
            <div class="stand-size" id="stand-size-verein">
                <p>
                    <label>Gewünschte Standfläche*</label>
                </p>
                <p>
                    <input type="text" name="stand_width" placeholder="Breite" required>
                </p>
                <p>
                    <input type="text" name="stand_depth" placeholder="Tiefe" required>
                </p>
            </div>
        </div>

        <p class="stand-prefered-address">
            <label>Wunschstandort angeben*<br/>(Bitte direkt aus dem Dropdown wählen)</label>
            <input type="text" name="prefered_address" placeholder="Straße + Hausnummer" autocomplete="off" required>
        </p>
        <div class="prefered-street-results">
            <ul></ul>
        </div>
        <p>
            <label>Angaben zum Aufbau</label>
        </p>
        <div class="checkbox-container">
            <input id="user_pavillon" type="checkbox" name="user_pavillon">
            <label for="user_pavillon">Ich möchte am Stand einen Pavillon aufbauen</label>
        </div>
        <div class="remarks-container">
            <h3>Sonstiges</h3>
            <p>Anmerkungen / Wünsche</p>
            <textarea name="user_remarks" id="user-remarks"
                      placeholder="Haben Sie besondere Anmerkungen zu Ihrem Aufbau, Wünsche oder ähnliches? Dann hinterlassen Sie uns hier eine kurze Nachricht."></textarea>
        </div>

        <div class="checkbox-container" id="agree_on_terms">
            <input type="checkbox" id="user_terms" name="user_terms" required>
            <label for="user_terms">Mir ist bewusst, dass dies keine verbindliche Bestellung ist, sondern lediglich eine Anfrage die von
                einem unserer Mitartbeiter geprüft werden muss</label>
        </div>

        <div class="button-row">
            <input type="submit" class="btn-primary disabled <?= wp_get_current_user()->roles[0] ?>" id="user-checkout"
                   value="Standbuchung anfragen"/>
            <a href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard] ?>" class="btn-secondary">Abbrechen</a>
        </div>
    </div>
</div>