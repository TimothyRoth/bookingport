<div class="form">
    <p>
        <label>Betriebe gewerblicher Art*</label>
        <select name="gastronomy">
            <option value="">Bitte auswählen</option>
            <option value="anlieger">Gewerblicher Anlieger/Gastronomie</option>
            <option value="gewerblich">Gewerbliche Gastronomie</option>
        </select>
    </p>

    <div class="further-steps">
        <h3>Standauswahl</h3>
        <div class="stand-size-selection-wrapper" id="stand-size-selection-wrapper-gewerblich">
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
            <div class="stand-size">
                <p>
                    <label>Gewünschte Standfläche*</label>
                </p>
                <p>
                    <input type="text" name="stand_width" placeholder="Breite" required>
                </p>
                <p class="hide-for-domestic">
                    <input type="text" name="stand_depth" placeholder="Tiefe" required>
                </p>
                <p class="show-for-domestic hide">
                    <?php
                    $bookingport_settings = new BOOKINGPORT_Settings();
                    $options_table = get_option($bookingport_settings::$option_table);
                    $price_per_meter = $options_table[$bookingport_settings::$option_price_per_meter];
                    ?>
                    <input class="prefilled-input" type="text" name="stand_price"
                           data-attribute="<?= $price_per_meter ?>" value="0" readonly>
                </p>
                <p class="show-for-domestic hide no-currency">
                    Preise sind nur Richtwerte und können vom späteren Endpreis abweichen
                </p>
            </div>
        </div>
        <p class="stand-prefered-address">
            <label>Wunschstandort angeben* <br/>(Bitte direkt aus dem Dropdown wählen)</label>
            <input type="text" name="prefered_address" placeholder="Straße + Hausnummer" autocomplete="off" required>
        </p>
        <div class="prefered-street-results">
            <ul></ul>
        </div>

        <p>
            <label>Angaben zum Aufbau</label>
        </p>
        <div class="checkbox-container">
            <input type="checkbox" id="user_pavillon" name="user_pavillon">
            <label for="user_pavillon">Ich möchte am Stand einen Pavillon aufbauen</label>
        </div>
        <div class="remarks-container">
            <h3>Sonstiges</h3>
            <p>Anmerkungen / Wünsche</p>
            <textarea name="user_remarks" id="user-remarks"
                      placeholder="Haben Sie besondere Anmerkungen zu Ihrem Aufbau, Wünsche oder ähnliches? Dann hinterlassen Sie uns hier eine kurze Nachricht."></textarea>
        </div>

        <div class="checkbox-container" id="agree_on_terms">
            <input id="user_terms" type="checkbox" name="user_terms" required>
            <label for="user_terms">Mir ist bewusst, dass dies keine verbindliche Bestellung ist, sondern lediglich eine
                Anfrage die von
                einem unserer Mitartbeiter geprüft werden muss.</label>
        </div>

        <div class="button-row">
            <input type="submit" class="btn-primary disabled <?= wp_get_current_user()->roles[0] ?>" id="user-checkout"
                   value="Standbuchung anfragen"/>
            <a href="/<?= $options_table[$bookingport_settings::$option_redirects_dashboard] ?>" class="btn-secondary">Abbrechen</a>
        </div>
    </div>
</div>