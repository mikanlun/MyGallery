$(function(){
    var intervalId;
    currentSet();
    setTimer();

    /**
     * 表示する画像を指定する
     */
    function currentSet() {
        $('.slide').find('li:first').addClass('current');
    }

    /**
     * タイマー
     */
    function setTimer(){
        // 画像表示タイマー
        intervalId = setInterval(autoClick, 5000);
        // サムネイルで表示するタイトルを取得するタイマー
        intervalIdTitle = setInterval(getTitle, 1000);
    }

    /**
     * 画像自動表示
     */
    function autoClick(){
         $('.slide').children('a.next').click();
    }

    /**
     * 画像をチェンジする
     */
    function changeImage($click){
        var $albumCnt;
        var $current;
        var findSelector = '';
        var $new;

        $albumCnt = $click.siblings('.album-container').children('li').length;

        if ($albumCnt > 1) {
            $current = $click.siblings('.album-container').children('.current');
            if($click.hasClass('next')){
                $new = $current.next();
                findSelector = ':first-child';
            } else {
                $new = $current.prev();
                findSelector = ':last-child';
            }

            if($new.length == 0) {
                $new = $current.siblings(findSelector);
            }
            $current.removeClass('current');
            $new.addClass('current');
        }
        setTimer();
    }


    /**
     * ウィンドウ上端でグローバルナビゲーションを固定する
     */
    $(window).on('scroll', function(){
        var scrollValue = $(this).scrollTop();
        $('.fixedmenu')
        .trigger('customScroll', {posY: scrollValue});
    });

    $('.fixedmenu')
    .each(function(){
        var $this = $(this);
        $this.data('initial', $this.offset().top);
    })
    .on('customScroll', function(event, object){

        var $this = $(this);

        if($this.data('initial') <= object.posY) {
            //要素を固定
            if(!$this.hasClass('fixed')) {
                var $substitute = $('<div></div>');
                $substitute
                .css({
                    'margin':'0',
                    'padding':'0',
                    'font-size':'0',
                    'height':'0'
                })
                .addClass('substitute')
                .height($this.outerHeight(true))
                .width($this.outerWidth(true));

                $this
                .after($substitute)
                .addClass('fixed')
                .css({top: 0});
            }
        } else {
            //要素の固定を解除
            $this.next('.substitute').remove();
            $this.removeClass('fixed');
        }
    });


    /**
     * スクロールしてページトップに戻る
     */
    var topBtn = $('#page-top');
    topBtn.hide();
    //スクロールが100に達したらボタン表示
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            topBtn.fadeIn();
        } else {
            topBtn.fadeOut();
        }
    });
    //スクロールしてトップ
    topBtn.click(function () {
        $('body,html').animate({
            scrollTop: 0
        }, 500);
        return false;
    });


/*****************/
/* スライドショー */
/*****************/

    /**
     * 画像をチェンジするイベント
     */
    $('.slide')
    .on('click', '> a', function(event){
        event.preventDefault();
        event.stopPropagation();
        clearInterval(intervalId);
        changeImage($(this));
    });

    /**
     * アルバム削除
     */
    $('#delete_image').on('click', function(){
        var $this = $(this);
        if(window.confirm('アルバムを削除しますがよろしいですか？')) {
            location.href = $this.data('url');
        } else {
            return false;
        }
    });

    /**
     * アカウント削除
     */
    $('#delete_account').on('click', function(){
        var $this = $(this);
        var userName;
        var msg;
        userName = $this.data('user_name');
        msg = userName + ' 様、アカウントを削除しますがよろしいですか？';
        if(window.confirm(msg)) {
            location.href = $this.data('url');
        } else {
            return false;
        }
    });


/*************/
/* サムネイル */
/*************/

    /**
     * サムネイルで表示する
     */
    $('.thumbnail').slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 2000,
        variableWidth: true,

    });

    /**
     * ユーザー単位にクラスを新たに付加する
     */
    $('.thumbnail').each(function(index) {
        var str = 'thumbnail' + (index + 1);
        $(this).addClass(str);
    });

    /**
     * サムネイルで表示するタイトルとユーザー名を取得する及び表示
     */
    function getTitle() {
        var userCnt = $('.slide').data('user_cnt');
        for (var i = 1; i <= userCnt; i++) {
            var thumbnail = '.thumbnail' + i;
            var $thumbnailCurrent = $(thumbnail).find('li.slick-current');
            var title = $thumbnailCurrent.find('img').data('title');
            var name = $thumbnailCurrent.find('img').data('name');
            $thumbnailCurrent.parents('.card-body').siblings('.card-header').children('.title').text(title);
            $thumbnailCurrent.parents('.card-body').siblings('.card-header').children('.name').text(name);
        }
    }

});
