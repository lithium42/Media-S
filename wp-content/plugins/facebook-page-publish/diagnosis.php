<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-EN">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Facebook Page Publish - Fault Diagnosis</title>
        <style type="text/css">
        body {
                font-family: "Lucida Grande", Verdana, Arial, "Bitstream Vera Sans", sans-serif;
                font-size: 14px;
                background-color: #FFF;
                padding: 2em;
        }
        
        img {
                border: 1em #EDEFF4 solid;
                margin: 1em 0em;
        }
        
        h1 {
                background-color: #3B5998;
                color: #FFF;
                font-size: 24px;
                padding: 0.3em;
        }
        
        h2 {
                background-color: #DEDEDE;
                padding: 0.3em;
                font-size: 16px;
        }
        
        a {
                color: red;
        }
        
        .keyword {
                color: gray;
                font-weight: bold;
        }
        
        a:target {
                background-color: yellow;
        }
        </style>
</head>

<body>
        <h1>Facebook Page Publish - Fault Diagnosis</h1>
        <h2>Check wether your server can connect to facebook</h2>
        
        <?php
        require_once( dirname(__FILE__) . '/../../../wp-load.php' );

        $request = new WP_Http;
        $api_url = 'https://graph.facebook.com/19292868552';
        $response = $request->get($api_url);
        
        if (array_key_exists('errors', $response)) {
                echo '<h3 style="color:red">There seems to be a problem</h3>';
                echo '<p>Try enabling the compatibility settings of the plugin</p>';
        } else {
                echo '<h3 style="color:green">Everything looks fine</h3>';
        }
        
        echo '<pre>';
        print_r($response);
        echo '</pre>';
        ?>
</body>
</html>
