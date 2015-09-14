$(document).ready(function() {

    $("#<?= $uid ?>").selectize({
        plugins: ['remove_button'],
        valueField: 'value',
        labelField: 'name',
        searchField: ['name'],
        create: false,
        preload: 'focus',
        load: function(query, callback) {
          //  if (!query.length) return callback();
            $.ajax({
                url: "<?= appurl::object_cb_data($rel, array('_ob' => 'name', '_obd' => 'asc') + $filter); ?>&_q=" + encodeURIComponent(query),
                type: 'GET',
                dataType: 'json',
                error: function() {
                    callback();
                },
                success: function(res) {
                    callback(res);
                }
            });
        },
        onInitialize: function() {
            var selected = this.$input.data('selected');
            if (selected) {
                for (var i in selected) {
                    if (selected.hasOwnProperty(i)) {
                        this.addOption(selected[i]);
                        this.addItem(selected[i].value);
                    }
                }
            }
        }
    });

});

