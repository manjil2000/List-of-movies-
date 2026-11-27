<?php
session_name('cinevault');
session_set_cookie_params(['path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
session_start();
header("Content-Security-Policy: default-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
require dirname(__DIR__,2).'/vendor/autoload.php';
$config = [
    'username' => '2583911',
    'password' => 'Chitwan@Nepal@2025'
];
$db = new PDO('mysql:host=localhost;dbname=db2583911;charset=utf8mb4',$config['username'],$config['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
$twig = new Twig\Environment(new Twig\Loader\FilesystemLoader(dirname(__DIR__).'/templates'),['autoescape'=>'html']);
function input(array $a,string $k): string {return is_string($a[$k]??null)?trim($a[$k]):'';}
function token(): string {return $_SESSION['csrf']??=bin2hex(random_bytes(32));}
function csrf(): void {if(!hash_equals(token(),input($_POST,'csrf'))){http_response_code(403);exit('Invalid request');}}
function go(string $url): never {header('Location: '.$url);exit;}
function logged(): void {if(empty($_SESSION['uid']))go('login.php');}
function render(string $page,array $data=[]): void {global $twig;echo $twig->render($page,$data+['csrf'=>token(),'user'=>$_SESSION['username']??null]);}
function movie_id(): int {$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);if(!$id){http_response_code(400);exit('Invalid ID');}return $id;}
function movie(int $id): array {global $db;$s=$db->prepare('SELECT * FROM movies WHERE Movie_id=?');$s->execute([$id]);$r=$s->fetch();if(!$r){http_response_code(404);exit('Movie not found');}return $r;}
function search_movies(array $a): array {global $db;$w=[];$p=[];$name=input($a,'name');$genre=input($a,'genre');$year=input($a,'year');$price=input($a,'price');if($name!==''){$w[]="Movie_name LIKE ? ESCAPE '!'";$p[]='%'.str_replace(['!','%','_'],['!!','!%','!_'],$name).'%';}if($genre!==''){$w[]='Genre=?';$p[]=$genre;}if($year!==''){if(!preg_match('/^[0-9]{4}$/D',$year))throw new InvalidArgumentException('Invalid year');$w[]='YEAR(Date_of_release)=?';$p[]=$year;}if($price!==''){if(!is_numeric($price)||!is_finite((float)$price)||(float)$price<0)throw new InvalidArgumentException('Invalid price');$w[]='Price<=?';$p[]=$price;}$s=$db->prepare('SELECT * FROM movies'.($w?' WHERE '.implode(' AND ',$w):'').' ORDER BY Movie_name, Movie_id');$s->execute($p);return $s->fetchAll();}
