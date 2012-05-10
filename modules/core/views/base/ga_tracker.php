<?php if (($ga_code = AppConfig::instance()->get('code', 'google_analytics')) != NULL): ?>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '<?= $ga_code;?>']);
<?php if (($ga_domain = AppConfig::instance()->get('domain', 'google_analytics')) != NULL): ?>
_gaq.push(['_setDomainName', '<?= $ga_domain;?>']);
<?php endif ?>
_gaq.push(['_trackPageview']);
_gaq.push(['_trackPageLoadTime']);
(function() {
 var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
 ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
 var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
 })();
</script>
<?php endif ?>