<?php

namespace MyApp;

class Todo {
    private $_db;

    //***************
    //コンストラクタ
    //***************
    /* トークンを発行し、DBに接続する。
     */
    public function __construct(){
        $this->_createToken();

        //DB接続
        try{
            $this->_db = new \PDO(DSN, DB_USERNAME, DB_PASSWORD);
            $this->_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }catch (\PDOException $e) {
            echo $e->getMessage();
            exit;
        }
    }

    //********************
    // トークン発行用関数
    //********************
    private function _createToken(){
        if(!isset($_SESSION['token'])){
            $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(16));
        }
    }

    //*********************
    // データ一覧取得用関数
    //*********************
    public function getAll(){
        $stmt = $this->_db->query("SELECT * FROM todos ORDER BY id DESC");
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    //**********************
    // POST判定用関数
    //**********************
    /* POSTで受け取ったモードに応じて、TODOリストの更新、登録、削除を行う。
     * update:更新
     * create:登録
     * delete:削除
     */
    public function post(){
        $this->_validateToken();

        if(!isset($_POST['mode'])){
            throw new \Exception('モードがセットされていません。');
        }

        switch($_POST['mode']){
            case 'update':
                return $this->_update();
            case 'create':
                return $this->_create();
            case 'delete':
                return $this->_delete();
        }
    }

    //************************
    // トークンチェック用関数
    //************************
    private function _validateToken(){
        if(!isset($_SESSION['token']) ||
           !isset($_POST['token']) ||
           $_SESSION['token'] !== $_POST['token']){
              throw new \Exception('invalid token!');
        }
    }

    //************************
    // TODOリスト更新用関数
    //************************
    /* 完了したリストのステータスを0から1に更新する。
     * state: 0 : 未完了
     * state: 1 : 完了
     */
    private function _update(){
        if(!isset($_POST['id'])){
            throw new \Exception('＜更新＞IDがセットされていません。');
        }

        $this->_db->beginTransaction();
        $sql = sprintf("UPDATE todos SET state= (state + 1) %% 2 WHERE id = %d", $_POST['id']);
        $stmt = $this->_db->prepare($sql);
        $stmt->execute();

        $sql = sprintf("SELECT state FROM todos WHERE id = %d", $_POST['id']);
        $stmt = $this->_db->query($sql);
        $state = $stmt->fetchColumn();

        $this->_db->commit();

        return [
            'state' => $state
        ];
    }

    //**************************
    // TODOリスト新規登録用関数
    //**************************
    private function _create(){
        if(!isset($_POST['title']) || $_POST['title'] === ''){
            throw new \Exception('＜登録＞タイトルがセットされていません。');
      }

        $sql = "INSERT INTO todos(title) VALUES(:title)";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute([':title' => $_POST['title']]);

        return [
            'id' => $this->_db->lastInsertId()
        ];
    }

    //***************************
    // TODOリスト削除用関数
    //***************************
    private function _delete(){
          if(!isset($_POST['id'])){
              throw new \Exception('＜削除＞IDがセットされていません。');
          }

          $sql = sprintf("delete from todos WHERE id = %d", $_POST['id']);
          $stmt = $this->_db->prepare($sql);
          $stmt->execute();

          return [];
    }
}
