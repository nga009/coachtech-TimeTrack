# coachtech勤怠管理

## 環境構築
1. リポジトリをクローン
``` bash
git clone git@github.com:nga009/coachtech-TimeTrack.git
```
2. DockerDesktopアプリを立ち上げる
3. プロジェクト直下で、以下のコマンドを実行する
``` bash
make init
```
※お使いの環境にmakeコマンドがインストールされていなく上述のコマンドがエラーになる場合は以下のコマンドを実行してください
``` bash
sudo apt install make
```

## テストアカウント
```
管理者
name: 管理者 
email: admin@example.com  
password: password  

一般ユーザー
name: 山田太郎 
email: yamada@example.com  
password: password  
-------------------------
name: 佐藤花子 
email: sato@example.com  
password: password  
-------------------------
※2025/10/1～2025/11/8までのサンプル勤怠データあり
```

## PHPUnitを利用したテストに関して
**環境構築**
以下のコマンドを実行:  
```
//テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
//パスワードはrootと入力
create database demo_test;

docker-compose exec php bash
php artisan key:generate --env=testing
php artisan config:clear
php artisan migrate:fresh --env=testing
```

**Unitテスト実行**
```
docker-compose exec php bash
php artisan test
```


## 使用技術(実行環境)
- PHP 8.1
- Laravel Framework 8.83.8
- MySQL 8.0.26
- mailhog 最新バージョン

## URL
- 開発環境(一般ユーザーログイン)：http://localhost/login
- 開発環境(管理者ユーザーログイン)：http://localhost/admin/login
- phpMyAdmin：http://localhost:8080/
- mailhog：http://localhost:8025

