<?php

use QueryBuilder\Builder\QueryBuilder;
require '../src/builder/QueryBuilder.php';
require '../src/helpers/Arr.php';
$qb = new QueryBuilder();

echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select(\'*\')->getAll();') .'</code>';
$select = $qb->table('candidates')->select('*')->getAll();

foreach ($select as $candidate) {
    echo '<p>'.$candidate['voornaam'].'</p>';
}
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select(\'voornaam\')->where(\'id\', \'!=\', 1)->getAll();') .'</code>';
$select = $qb->table('candidates')->select('voornaam')->where('id', '!=', 1)->getAll();

foreach ($select as $candidate) {
    echo '<p>'.$candidate['voornaam'].'</p>';
}
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select(\'*\')->count();') .'</code>';
$count = $qb->table('candidates')->select('*')->count();
echo '<p>' . $count . '<p>';
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$fields = [\'voornaam AS name\', \'achternaam\'];') .'</code><br>';
echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select($fields)->getAll();') .'</code>';
$fields = ['voornaam AS name', 'achternaam'];
$select = $qb->table('candidates')->select($fields)->getAll();

foreach ($select as $candidate) {
    echo '<p>' . $candidate['name'] . ' ' . $candidate['achternaam'] . '</p>';
}
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$fields = [\'voornaam\', \'achternaam AS last\'];') .'</code><br>';
echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select($fields)->where(\'id\', 1)->get();') .'</code>';
$fields = ['voornaam', 'achternaam AS last'];
$select = $qb->table('candidates')->select($fields)->where('id', 1)->get();

echo '<p>' . $select['voornaam'] . ' ' . $select['last'] . '</p>';
echo '<hr>';

echo '<code style="color: darkgreen">' . htmlentities('$qb->table(\'candidates\')->select(\'*\')->orderBy(\'id\', \'DESC\')->getAll();') .'</code>';
$select = $qb->table('candidates')->select('*')->orderBy('id', 'DESC')->getAll();

foreach ($select as $candidate) {
    echo '<p>' . $candidate['id'] . '</p>';
}
echo '<hr>';



