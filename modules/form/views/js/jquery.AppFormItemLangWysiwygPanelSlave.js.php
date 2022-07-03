// <script>
(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemLangWysiwygPanelSlave';

    /**
     * Metody pro tento plugin.
     */
    var methods = {

        init: function( options ) {

            /**
             * Defaultni hodnoty pro parametry a nastaveni pluginu
             */
            var settings = {
                placeholders: {},
                attr: ''
            };

            /**
             * this je nyni neco jako jQuery iterator - to co vratil selector
             * volanim each zajistime provedeni definovane funkce
             * postupne v kontextu kazdeho z vybranych elementu */
            return this.each(function() {

                /**
                 * nyni je this pouze js objekt, z nej vytvorime jQuery objekt
                 * s nazvem $this a dale pracujeme s nim - jedna se o uzel divu
                 * okolo form item */
                var $this = $(this);

                // Najdeme parent formular
                var $form = $this.parents(".<?= AppForm::FORM_CSS_CLASS ?>:first");

                //Pokud prislo nastaveni, tak mergnu s defaultnimi hodnotami
                var params = $.extend(true, settings, options);

                // Jake je prave aktivni lokale
                var active_locale = false;

                var initWysiwyg = function(){
                    $this.find('[data-locale]').each(function () {
                        tinymce.init({
                            selector: '#' + $(this).attr('id'),
                            plugins: 'autolink link table anchor lists checklist',
                            toolbar_mode: 'floating',
                            toolbar: 'undo redo bold italic underline strikethrough forecolor backcolor fontsize | alignleft aligncenter alignright alignjustify numlist bullist checklist link table',
                            menubar: ''
                        });
                    });
                }

                /**
                 * activeLocaleChanged event handler
                 * @param event
                 * @param locale
                 * @param no_focus - after formInit we do not want to have a focus in Wysiwyg editor
                 */
                var onActiveLocaleChanged = function(event, locale) {
                    $this.find('[data-locale]').each(function () {
                        if ($(this).data('locale') === locale) {
                            $(this).parent().show();
                        } else {
                            $(this).parent().hide();
                        }
                    });
                };

                // Inicializujeme wysiwyg editor
                initWysiwyg();

                // Na udalost activeLocaleChanged budeme menit hodnotu v inputu - hodime tam preklad pro dane locale
                // - nejprve volame unbind, protoze po ulozeni formulare dochazi k opetovne inicializaci prvku, zatimco form
                //   je stale stejny
                var event_name = 'activeLocaleChanged.' + settings.attr;
                $form.off(event_name).on(event_name, onActiveLocaleChanged);

                onActiveLocaleChanged(null, $form.objectForm('getDefaultLocale'));

            }); // end each

        } // end init

    };


    $.fn.AppFormItemLangWysiwygPanelSlave = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemLangWysiwygPanelSlave');

        }

        return this;

    };

})( jQuery );

