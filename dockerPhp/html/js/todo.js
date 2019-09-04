$(function(){
    'use strict';

    $('#new_todo').focus();

    //*****************
    // 更新
    //*****************
    /* ステータスが1の場合"done"のクラス名を追加し、
     * ステータスが0の場合"done"のクラスを削除する。
    */
    $('#todos').on('click', '.update_todo', function(){
        //IDを取得する
        var id = $(this).parents('li').data('id');

        //Ajax処理
        $.post('_ajax.php', {
            id: id,
            mode: 'update',
            token: $('#token').val()
        }, function(res){
            if(res.state === '1'){
                $('#todo_' + id).find('.todo_title').addClass('done');
            }else{
                $('#todo_' + id).find('.todo_title').removeClass('done');
            }
        })
    });

    //*******************
    // 削除
    //*******************
    $('#todos').on('click', '.delete_todo', function(){
        //IDを取得する
        var id = $(this).parents('li').data('id');

        //Ajax処理
        if(confirm('本当に削除してよろしいですか？')){
            $.post('_ajax.php', {
                id: id,
                mode: 'delete',
                token: $('#token').val()
            }, function(){
                $('#todo_' + id).fadeOut(800);
            });
        }

    });

    //******************
    // 登録
    //******************
    $('#new_todo_form').on('submit', function(){

        //タイトルを取得する
        var title = $('#new_todo').val();

        //Ajax処理
        $.post('_ajax.php', {
            title: title,
            mode: 'create',
            token: $('#token').val()
        }, function(res){
            var $li = $('#todo_template').clone();
            $li
                .attr('id', 'todo_' + res.id)
                .data('id', res.id)
                .find('.todo_title').text(title);
            $('#todos').prepend($li.fadeIn());
            $('#new_todo').val('').focus();
        });
        return false;
    });
});
