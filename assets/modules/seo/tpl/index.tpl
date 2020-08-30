<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <title>SEO <?=$res['version']?></title>
    <meta charset="UTF-8">
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap-combined.min.css" rel="stylesheet">
    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js"></script>
    <style type="text/css">
        form {margin-bottom:0px;}
        input[type=text] {margin-bottom:0px;}
        .navbar-inner {padding:0px;}
        #EditAreaArroundInfos_myeditarea {display: none;}
    </style>
    <script type="text/javascript">
        $(document).ready(function(){
            $("[data-active]").each(function(){
               _option = $(this).find("option[value='"+$(this).attr("data-active")+"']");
               _option.prop("selected", true);
            });
            $("[title]").tooltip();
            $(".update").on("click", function(e){
              e.preventDefault();
              $(this).parents("tr").wrap("<form method='post' />");//find("input, select")
              $(this).parents("form").submit();
            })
        });
    </script>
</head>
<body>
    <div class="container">
      <div class="masthead">
        <p></p>
        <div class="navbar">
          <div class="navbar-inner">
            <div class="container">
              <ul class="nav">
                <li<?=$get == "redirect" ? ' class="active"' : '' ?>><a href="<?=$url?>&get=redirect"><span class="icon icon-retweet"></span> Перенаправления</a></li>
                <li<?=$get == "robots" ? ' class="active"' : '' ?>><a href="<?=$url?>&get=robots"><span class="icon icon-adjust"></span> Robots.txt</a></li>
                <li<?=$get == "sitemap" ? ' class="active"' : '' ?>><a href="<?=$url?>&get=sitemap"><span class="icon icon-map-marker"></span> Карта сайта</a></li>
                <li<?=$get == "counters" ? ' class="active"' : '' ?>><a href="<?=$url?>&get=counters"><span class="icon icon-globe"></span> Счетчики</a></li>
              </ul>
            </div>
          </div>
        </div><!-- /.navbar -->
      </div>
      <? if (isset($res['alert'])): ?>
        <div class="alert fade in">
          <button type="button" class="close" data-dismiss="alert">×</button>
          <strong><?=$res['alert']?></strong>
        </div>
      <? endif ?>
      <?=$res['content']?>
    </div>
</body>
</html>