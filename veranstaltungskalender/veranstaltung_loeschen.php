<?php 
require_once 'functions_veranstaltungen.php';

/** @var int $loeschid ID des geschriebenen Datensatzes */
$loeschid = !empty($_GET['loeschid']) ? intval(trim($_GET['loeschid'])) : 0;

/** @var string $loeschok  Löschbestätigung */
$loeschok = $_GET['loeschok'] ?? false;


/** @var string $ergebnis  Fehlermeldung */
$ergebnis = '';

/** @var string[] $veranstaltung Daten des gespeicherten Eintrags */
$veranstaltung = [];

// Wenn gültige Datensatz-ID übergeben wurde
if(0 < $loeschid && veranstaltungExist($loeschid)) {
    
    // Verbindung zur Datenbank aufbauen
    $db = dbConnect(); 

    // Prüfen, ob Löschbestätigung gegeben wurde
    if(!$loeschok) {
        /*
         * Erfasste Daten aus der Datenbank lesen,
         * wenn noch keine Löschbestätigung gegeben wurde
         */
        // SQL-Statement erzeugen
        $sql = <<<EOT
            SELECT vid, 
                   name,
                   SUBSTR(beschreibung,1,40) AS beschreibung, 
                   oid, 
                   DATE_FORMAT(datum, '%d.%m.%Y') AS datum
            FROM veranstaltungen 
            WHERE vid = $loeschid
EOT;

        // SQL-Statement an die Datenbank schicken und Ergebnis (Resultset) in $result speichern
        if($result = mysqli_query($db, $sql)) {
            // Den ersten (und einzigen) Datensatz aus dem Resultset holen
            if($veranstaltung = mysqli_fetch_assoc($result)) {
                // Felder für die Ausgabe in HTML-Seite vorbereiten
                foreach($veranstaltung as $key => $value) {
                    $veranstaltung[$key] = htmlspecialchars($value);
                }
            }

            // Resultset freigeben
            mysqli_free_result($result);
        }
        else {
            die('DB-Fehler (' . mysqli_errno($db) . ') ' . mysqli_error($db));
        }
    }
    // Lösch-Bestätigung erhalten
    else {
        /*
         * Datensatz löschen
         */
        // SQL-Statement erzeugen
        $sql = "DELETE FROM veranstaltungen WHERE vid = $loeschid"; // WHERE NICHT VERGESSEN!!!
        
        // Statement an die DB schicken
        mysqli_query($db, $sql) || die('DB-Fehler');
        
        // Verbindung zur Datenbank trennen
        mysqli_close($db);
        
        // Weiterleiten auf Bestätigungsseite
        header("location: veranstaltung-loeschen-ok.php");
        
    }

    // Verbindung zur Datenbank trennen
    mysqli_close($db);
}
elseif(!$loeschid) {
    // Datensatz-ID wurde nicht übergeben
    $ergebnis = 'Datensatz-ID fehlt!';
}
else {
    // Datensatz mit dieser ID existiert nicht
    $ergebnis = 'Ungültige Datensatz-ID!';
}
$ausgabe['titel'] = 'Löschen';
include TEMPLATES . 'htmlkopf.phtml';
?>

            <?php if($ergebnis): ?>
            <h3><span><?= $ergebnis ?></span></h3>
            
            
            <?php else: ?>
            <h4><span>Soll dieser Eintrag wirklich gelöscht werden?</span></h4>
            
            <table>
                <?php foreach($veranstaltung as $name => $wert): ?>
                <tr>
                    <th><?= ucfirst($name) ?></th>
                    <td><?= $wert ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
                <input type="hidden" name="loeschid" value="<?= $veranstaltung['vid'] ?>">
                <div class="center">
                    <button type="submit" name="loeschok" value="1">löschen</button>
                </div>
            </form>
            
            <?php endif; ?>
            <h3><a href="pindexadmin.php">zurück zur Liste</a></h3>
       <?php
include TEMPLATES . 'htmlfuss.phtml';