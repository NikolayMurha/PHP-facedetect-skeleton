<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
<head>

    <title>TuneHog Discovery</title>
    <meta http-equiv="content-type" content="text/html; charset=utf8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=9"/>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>

    {$null=$this->headLink()->appendStylesheet('/css/style.css')}
    {$null=$this->headScript()->appendFile('/js/jquery-1.8.3.js')}
    {$this->headLink()}
    {* <link rel="stylesheet" type="text/css" href="//assets.zendesk.com/external/zenbox/v2.5/zenbox.css" media="screen, projection"> *}
    {$this->headScript()}

</head>
<body>
    <div id="content">{$this->layout()->content}</div>
</body>
</html>