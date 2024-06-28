<?php 

// проверка константы для запрета прямомого доступа к файлу config .php
defined('VG_ACCESS') or die('Access deny');


// адрес и корневой путь сайта
const SITE_URL = 'http://localhost';
const PATH = '/';

// Подключение к базе данных хост, пользователь, пароль, имя баз данных
const HOST = 'localhost';
const USER = 'root';
const PASSWORD = 'root';
const DB = 'im';

