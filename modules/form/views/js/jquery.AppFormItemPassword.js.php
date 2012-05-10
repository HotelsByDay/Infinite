

(function( $ ){

    /**
     * Nazev pluginu. Pouziva se jako namespace napriklad v metode data().
     * Pokud je potreba zmenit tak zde a pak nize v hlavni metode ($.fn.___).
     */
    var plugin_name_space = 'AppFormItemPassword';

    /**
     * Defaultni hodnoty pro parametry a nastaveni pluginu
     */
    var settings = {
    };

    /**
     * Metody pro tento plugin.
     */
    var methods = {

        init: function( options ) {

            //Pokud prislo nastaveni, tak mergnu s defaultnimi hodnotami
            if ( options ) {
                $.extend( settings, options );
            }

            return this.each(function(){

                var $this = $(this);

                //ulozim si aktualni nastaveni pluginu
                methods._setData( $this , {
                    settings: settings
                });

                //reference na policka pro vlozeni hesel
                $password         = $this.find('input[name$="[password]"]');
                $password_confirm = $this.find('input[name$="[password_confirm]"]');

                //blok, ktery informuje uzivatele o sile hesla
                $password_strength_info = $this.find('.password_strength_info');

                //zde je zobrazena zprava informujici o sile hesla
                $password_strength_message = $password_strength_info.find('.message');

                //blok, ktery zobrazuje informaci o tom ze se vlozena hesla neshoduji
                $passwords_dont_match_message = $this.find('.passwords_dont_match');

                //handler ktery pouziju pri zmene hodnoty jednoho ze dvou poli pro zmenu hesla
                var password_change_handler = function() {


                    if ($password.val() != $password_confirm.val()) {
                        $passwords_dont_match_message.show();
                        $password_strength_info.hide();
                    } else {
                        $passwords_dont_match_message.hide();
                        $password_strength_info.show();
                        
                        //ziskam silu hesla
                        var pwd_level = methods._passwordStrength($this, $password.val());

                        //odeberu vsechny classy, ktere informovaly o predchozi urovni hesla
                        $password_strength_info.removeClass('level1 level2 level3 level4');

                        //nastavim css tridu podle nove urovne
                        $password_strength_info.addClass('level' + pwd_level);

                        switch (pwd_level) {
                            case 1:
                                $password_strength_message.html("<?= __('appformitempassword.pwd_strength_level_1_message');?>");
                            break;

                            case 2:
                                $password_strength_message.html("<?= __('appformitempassword.pwd_strength_level_2_message');?>");
                            break;

                            case 3:
                                $password_strength_message.html("<?= __('appformitempassword.pwd_strength_level_3_message');?>");
                            break;

                            case 4:
                                $password_strength_message.html("<?= __('appformitempassword.pwd_strength_level_4_message');?>");
                            break;
                        }
                    }
                };

                //obsluha pri zmene hodnoty
                $password.keyup(password_change_handler);
                $password_confirm.keyup(password_change_handler);

            });

        },

        /**
         * Funkce pocita silu uzivatelskeho hesla.
         * Vraci celociselnou hodnotu 1-4 kde:
         *  1 ... prilis kratke heslo (heslo musi byt alespon 8 znaku dlouhe)
         *  2 ... slabe heslo
         *  3 ... dobre heslo
         *  4 ... silne heslo
         *
         */
        _passwordStrength: function ( $this, password ) {

            score = 0;

            //password < 4
            if (password.length < 8 ) { return 1; }

            //password length
            score += password.length * 4;
            score += ( methods._checkRepetition($this,1,password).length - password.length ) * 1;
            score += ( methods._checkRepetition($this,2,password).length - password.length ) * 1;
            score += ( methods._checkRepetition($this,3,password).length - password.length ) * 1;
            score += ( methods._checkRepetition($this,4,password).length - password.length ) * 1;

            //password has 3 numbers
            if (password.match(/(.*[0-9].*[0-9].*[0-9])/))  score += 5 ;

            //password has 2 sybols
            if (password.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)) score += 5 ;

            //password has Upper and Lower chars
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))  score += 10 ;

            //password has number and chars
            if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/))  score += 15 ;

            //password has number and symbol
            if (password.match(/([!,@,#,$,%,^,&,*,?,_,~])/) && password.match(/([0-9])/))  score += 15 ;

            //password has char and symbol
            if (password.match(/([!,@,#,$,%,^,&,*,?,_,~])/) && password.match(/([a-zA-Z])/))  score += 15 ;

            //password is just a nubers or chars
            if (password.match(/^\w+$/) || password.match(/^\d+$/) )  score -= 10 ;

            //verifing 0 < score < 100
            if ( score < 0 )  score = 0 ;
            if ( score > 100 )  score = 100;

            if (score < 34 )  return 2 ;
            if (score < 68 )  return 3 ;
            return 4 ;

        },

        _checkRepetition: function ( $this , len , str) {

            res = "";
            for ( i=0; i<str.length ; i++ ) {
                repeated = true;
                for (j=0;j < len && (j+i+len) < str.length;j++) {
                    repeated = repeated && (str.charAt(j+i)==str.charAt(j+i+len));
                }

                if (j<len) {
                    repeated = false;
                }

                if (repeated) {
                    i += len-1;
                    repeated=false;
                } else {
                    res += str.charAt(i);
                }
            }
            return res
        },

        /**
         *
         */
        _setData: function( $this, key, value ) {

            if (typeof key === 'object' ) {

                var current_data = $this.data( plugin_name_space);

                if (typeof current_data === 'undefined') {
                    current_data = new Object();
                }

                //budu extendovat to co mam aktualne v datech ulozene
                $.extend( current_data , key);

                $this.data( plugin_name_space, current_data );

            } else {

                var current_data = $this.data( plugin_name_space );

                if (typeof current_data === 'undefined' ) {
                    current_data = {
                        key: value
                    };
                } else {
                    current_data[key] = value;
                }

                $this.data( plugin_name_space, current_data )

            }

        },

        /**
         *
         */
        _getData: function( $this, key ) {

            var current_data = $this.data( plugin_name_space );

            return current_data[ key ];

        },

        _log: function( text ) {
            if ( typeof console !== 'undefined') {
                console.log( text );
            }
        }

    };

    $.fn.AppFormItemPassword = function( method ) {

        //Logika pro volani metod
        if ( methods[ method ] ) {

            return methods[ method ].apply( Array.prototype.slice.call( arguments , 1 ));

        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );

        } else {

            $.error( 'Method ' + method + ' does not exist on jQuery.AppFormItemPassword');

        }

        return this;

    };

})( jQuery );