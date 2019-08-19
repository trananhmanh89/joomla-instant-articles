<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;
?>

<script>
    function checkLoginState() {
        FB.getLoginStatus(function (response) {
            if (response.status === 'connected') {
                jQuery('.login-button').hide();
                jQuery('.pages').html( 'Getting page list ....' );
                jQuery.ajax({
                    url: '<?php echo JUri::current() . '?option=com_d4jinstant&task=setup.getPages' ?>',
                    dataType: 'json',
                    data: { token: response.authResponse.accessToken }
                }).success(function( json ) {
                    if (json) {
                        getPages(json);
                    } else {
                        alert(' You have no page that supports Instant Article. Please sign up new one.');
                        location.reload();
                    }
                }).error( function() {
                    alert('Login failed! Please check appid or secret.');
                    location.reload();
                });
            } else {
                alert('Login failed!');
                location.reload();
            }
        });
    }

    function getPages(pages) {
        if (pages.length) {
            var html = pages.map( function( page ) {
                return [
                    '<div class="radio">',
                        '<label>',
                            '<img src="'+page.picture.url+'" />',
                            '<input style="margin-top: 18px" type="radio" name="pageid" value="'+page.id+'" >',
                            ' ' + page.name,
                        '</label>',
                    '</div>'
                ].join('');
            }).join('');
            html += '<p style="margin-top: 20px; margin-left: 20px;"><a href="javascript:;" onclick="Joomla.submitbutton(\'setup.savePage\');" class="btn btn-success">Save</div></p>';
            jQuery('.pages').html( html );
        } else {
            alert(' You have no page that supports Instant Article. Please sign up new one.');
            location.reload();
        }
    }

    function choosePage() {
        var checked = jQuery('input[name=page]:checked');
        if (checked.length) {
            console.log(checked.attr('token'));
            console.log(checked.attr('pageid'));
            
        } else {
            alert('Please choose a page!');
        }
    }

    window.fbAsyncInit = function () {
        FB.init({
            appId: '<?php echo $this->appid ?>',
            cookie: true,
            xfbml: true,
            version: 'v4.0'
        });

        FB.AppEvents.logPageView();

    };

    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = "https://connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
</script>

<div class="login-button">
    <fb:login-button 
        scope="manage_pages,pages_manage_instant_articles,publish_pages"
        onlogin="checkLoginState();">
    </fb:login-button>
</div>

<form method="post" name="adminForm" id="adminForm" >
    <div class="pages"></div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>

