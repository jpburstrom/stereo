<html>
<head>
    <title>This is the new random(<?=rand(2,44)?>) title</title>
</head>
<body>
<script type="text/javascript">console.log("foo" + <?=time()?>);</script>
    <div class="box" id="content">

    This is a test. Some random content: <?=rand(2, 255)?>
    </div>
    <div class="box" id="sidebar">
    This could be a sidebar. Some random content: <?=rand(2, 255)?>
    </div>
</body>

