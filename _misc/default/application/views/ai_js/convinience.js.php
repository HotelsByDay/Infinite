/**
 *
 */
function initObjectFilterAutocomplete($visible_input, $hidden_input, data_url) {

    var onFocusHandler = function(){
        if (this.value == "") {
            $(this).autocomplete('search', '');
        }
    };
    var onChangeHandler = function(){
        $visible_input.val('');
        $hidden_input.val('');
    };

    //inicializace naseptavace na inputu pro zadani nazvu maklere
    $visible_input.autocomplete({
        source: function( request, response ) {
            $.ajax({
                url: data_url,
                dataType: "json",
                data: {
                    featureClass: "P",
                    style: "full",
                    maxRows: 15,
                    <?= Filter_Base::FULLTEXT_QUERY_KEY;?>: request.term
                },
                success: function( data ) {
                    response( $.map( data, function( item ) {
                        return {
                            value: item.name,
                            id:    item.value,
                        }
                    }));
                }
            });
        },
        minLength: 0,
        select: function( event, ui ) {
            $hidden_input.val(ui.item.id);
        },
        open: function(){
            $(this).unbind('focus', onFocusHandler);
            $(this).unbind('change', onChangeHandler);
        },
        close: function(){
            $(this).bind('focus', onFocusHandler);
            $(this).bind('change', onChangeHandler);
        }
    }).focus(onFocusHandler).change(onChangeHandler);
}