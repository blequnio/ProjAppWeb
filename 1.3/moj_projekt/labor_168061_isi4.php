<?php
// Zadanie 1: Wyświetlanie podstawowych informacji
$nr_indeksu = '168061'; // Twój numer indeksu
$nrGrupy = 'isi4'; // Twój numer grupy
$imie = 'Łukasz'; // Twoje imię

echo 'Imię: '.$imie.'<br />';
echo 'Numer indeksu: '.$nr_indeksu.'<br />';
echo 'Grupa: '.$nrGrupy.'<br /><br />';

echo '<br />------------------------------------<br />';

// Zadanie 2: Demonstracja różnych funkcji PHP

// a) Demonstracja include() i require_once()
echo 'Demonstracja include() i require_once():<br />';
// Utwórz plik o nazwie plik_do_zalaczenia.php z przykładową treścią np. "<?php echo 'Zawartość pliku include!<br />'; ?>"
include 'plik_do_zalaczenia.php';
require_once 'plik_do_zalaczenia.php';

echo '<br />------------------------------------<br />';

// b) Przykład if, else, elseif, switch
echo 'Przykład warunków if/else i switch:<br />';
$liczba = 10;

if ($liczba > 5) {
    echo 'Większe niż 5<br />';
} elseif ($liczba == 5) {
    echo 'Równe 5<br />';
} else {
    echo 'Mniejsze niż 5<br />';
}

echo 'Przykład switch:<br />';
switch ($liczba) {
    case 10:
        echo 'Liczba to 10<br />';
        break;
    default:
        echo 'Nieznana liczba<br />';
}

echo '<br />------------------------------------<br />';

// c) Przykład pętli while() i for()
echo 'Przykład pętli while i for:<br />';

echo 'Pętla while:<br />';
$i = 0;
while ($i < 5) {
    echo 'Licznik: '.$i.'<br />';
    $i++;
}

echo 'Pętla for:<br />';
for ($j = 0; $j < 5; $j++) {
    echo 'Licznik: '.$j.'<br />';
}

echo '<br />------------------------------------<br />';

// d) Przykład $_GET, $_POST, $_SESSION
echo 'Przykład zmiennych $_GET, $_POST, $_SESSION:<br />';

// Rozpoczęcie sesji dla $_SESSION
session_start();

// $_GET
echo 'Przykład $_GET:<br />';
if (isset($_GET['name'])) {
    echo 'Parametr name: '.$_GET['name'].'<br />';
} else {
    echo 'Użyj URL z parametrem, np. ?name=Jan<br />';
}

echo '<br />';

// $_POST
echo 'Przykład $_POST:<br />';
echo '<form method="post">
        <input type="text" name="imie" placeholder="Podaj swoje imię">
        <button type="submit">Wyślij</button>
      </form>';
if ($_POST) {
    echo 'Twoje imię: '.$_POST['imie'].'<br />';
}

echo '<br />';

// $_SESSION
echo 'Przykład $_SESSION:<br />';
$_SESSION['test'] = 'Wartość sesji';
echo 'Zapisana wartość w sesji: '.$_SESSION['test'].'<br />';
?>
