<?php

use BITbuilder\core\Builder;
use BITbuilder\helpers\Database;

require '../src/core/Builder.php';
require '../src/core/Query.php';
require '../src/helpers/Arr.php';
require '../src/helpers/Str.php';
require '../src/helpers/Database.php';

$db = new Database();
$qb = new Builder($db);

echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select(\'*\')->getAll();') .'</code>';
$select = $qb->table('candidates')->select('*')->fetchAll();

foreach ($select as $candidate) {
    echo '<p>'.$candidate['voornaam'].'</p>';
}
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select(\'voornaam\')->where(\'id\', \'!=\', 1)->getAll();') .'</code>';
$select = $qb->table('candidates')->select('voornaam')->where('id', '!=', 1)->fetchAll();

foreach ($select as $candidate) {
    echo '<p>'.$candidate['voornaam'].'</p>';
}
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select(\'*\')->countRows();') .'</code>';
$count = $qb->table('candidates')->select('*')->countRows();
echo '<p>' . $count . '<p>';
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$fields = [\'voornaam AS name\', \'achternaam\'];') .'</code><br>';
echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select($fields)->getAll();') .'</code>';
$fields = ['voornaam AS name', 'achternaam'];
$select = $qb->table('candidates')->select($fields)->fetchAll();

foreach ($select as $candidate) {
    echo '<p>' . $candidate['name'] . ' ' . $candidate['achternaam'] . '</p>';
}
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$fields = [\'voornaam\', \'achternaam AS last\'];') .'</code><br>';
echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select($fields)->where(\'id\', 4)->get();') .'</code>';
$fields = ['voornaam', 'achternaam AS last'];
$select = $qb->table('candidates')->select($fields)->where('id', 4)->fetch();

echo '<p>' . $select['voornaam'] . ' ' . $select['last'] . '</p>';
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select(\'*\')->orderBy(\'id\', \'DESC\')->getAll();') .'</code>';
$select = $qb->table('candidates')->select('*')->orderBy('id', 'DESC')->fetchAll();

foreach ($select as $candidate) {
    echo '<p>' . $candidate['id'] . '</p>';
}
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$select = $qb->table(\'candidates\')->select([\'voornaam\', \'achternaam\'])->where(\'id\', \'>\', 1)->and_(\'voornaam\', \'!=\', \'frank\')->getAll();') .'</code>';

$select = $qb->table('candidates')
            ->select(['voornaam', 'achternaam'])
            ->where('id', '>', 1)
            ->and_('voornaam', '!=', 'frank')
            ->fetchAll();

foreach ($select as $candidate) {
    echo '<p>' . $candidate['voornaam'] . ' ' . $candidate['achternaam'] . '</p>';
}
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select(\'achternaam\')->orderBy(\'achternaam\')->limit(4)->getAll();') .'</code>';
$select =  $qb->table('candidates')->select('achternaam')->orderBy('achternaam')->limit(4)->fetchAll();

foreach ($select as $candidate) {
    echo '<p>' . $candidate['achternaam'] . '</p>';
}
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$qb->raw(\'SELECT tussenvoegsel FROM candidates\')->getAll();') .'</code>';
$select = $qb->raw('SELECT tussenvoegsel FROM candidates')->fetchAll();
foreach ($select as $candidate) {
    echo '<p>' . $candidate['tussenvoegsel'] . '</p>';
}
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities(' $qb->table(\'vacancies v\')->select([\'v.title\', \'v.description\'])->join(\'candidates c\', \'v.cid\', \'c.id\')->getAll();') .'</code>';
$select = $qb->table('vacancies v')
            ->select(['v.title', 'v.description'])
            ->join('candidates c', 'v.cid', '=', 'c.id')
            ->fetchAll();

foreach ($select as $vacancy) {
    echo '<p>' . $vacancy['title'] . '</p>';
    echo '<p>' . $vacancy['description'] . '</p>';
}