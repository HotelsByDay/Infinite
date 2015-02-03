$(document).ready(function(){
    var $this = $('#<?= $uid ?>');
    $this.find('.items').buttonset();

    // @todo - hack fixing buttonset in Chrome ~31+ (this can be removed after migration to the new bootstrap version)
    $this.find('.items input:radio').removeClass('ui-helper-hidden-accessible');
    setTimeout(function(){$this.find('.items input:radio').addClass('ui-helper-hidden-accessible');}, 1);
});

