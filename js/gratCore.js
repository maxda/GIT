
/**
 * AJAX toltip basato su qTip
 * ricerca l'attributo "tipurl" che contiente l'url ajax per il contenuto del tol tip
 * e se lo trova imposta il messaggio sull'elemento
 */

Drupal.behaviors.gratCore = function (context) {
// qtip connection
    $jq('[tipurl]').each(function () {
        $jq(this).qtip({
            content: {
                title: 'info rapide',
                text: 'caricamento...',
                ajax: {url: '?q=' + $jq(this).attr('tipurl')}
            },
            show: {event: 'mouseenter',
                solo: true
            },
            hide: {
                event: 'mouseleave',
                fixed: true
            },
            position: {
                at: 'top right', // Position the tooltip above the link
                my: 'bottom left'
            },
            style: {
                classes: 'ui-tooltip-cluetip ui-tooltip-shadow',
                width: '300px'
            }

        })
    });
// treeview connection
    $jq('#tree-message').each(function () {
        $jq(this).qtip(
                {
                    id: 'modal', // Since we're only creating one modal, give it an ID so we can style it
                    content: {
                        text: $jq('div.treeview'),
                        title: {
                            text: $jq(this).attr('dialogModal'),
                            button: true
                        }
                    },
                    position: {
                        my: 'top center', // ...at the center of the viewport
                        at: 'top center',
                        target: $jq(window)
                    },
                    show: {
                        event: 'click', // Show it on click...
                        solo: true, // ...and hide all other tooltips...
                        modal: true // ...and make it modal
                    },
                    hide: false,
                    style: {
                        classes: 'ui-tooltip-light ui-tooltip-rounded',
                        width: '760px'
                    },
                    events: {
                        hide: function (event, api) {
                            var list = '';
                            var struct_selection = '';
                            $jq('ul.treeview input[type="checkbox"]:checked').each(function () {
                                list = list + (list == '' ? '' : ', ') + $jq(this).parent().text();
                                struct_selection = struct_selection + (struct_selection == '' ? '' : ', ') + $jq(this).val();
                            });
                            $jq('input[name="struct_selection"]').val(struct_selection);
                            if (list == '')
                                list = 'Selezione struttura';
                            $jq('a#tree-message').text(list);

                        }
                    }
                });
    });

    $jq('ul.treeview').each(function () {
        $jq(this).treeview({
            /* collapsed:false */
        })
    });
    /*
     * seleziona o deseleziona tutti i checkbox figli 
     * */
    $jq('ul.treeview input[type="checkbox"]').each(function () {

        $jq(this).click(function () {
            var parent_id = $jq(this).attr('value'); /* il valore del checkbox è anche l'id del contenitore del sottoalbero*/
            var check = $jq(this).attr('checked');
            /* reimposta tutti i checkbox nell'<UL> contenuto  in questo li identificato con id*/
            $jq('ul.treeview li#' + parent_id + ' ul input[type="checkbox"]').attr('checked', check);
        });

    });
    // limitatore caratteri  
    $('input[type="text"]').bind('keypress', function (event) {
        var regint = new RegExp("[0-9]");//caratteri concessi per gli interi
        var regcmd = new RegExp("[\x08-\x2E]"); //filtra i comandi della tastiera
        var $this = $(this).val().length;
        var maxlenght = $(this).attr("size");//recupera dimensione
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode); //tasto catturato 

        if (!regint.test(key) & !regcmd.test(key) & $(this).hasClass('integer')) {
            event.preventDefault();
            return false;
        }
//       if ($this >= maxlenght & !regcmd.test(key)){
//           event.preventDefault();
//           return false;
//       }
    });

    // sparkline connection

    $jq('.sparkline').each(function () {
        // var settings=Drupal.gratcore.sparklineSettings;
        this.sparkline('html', {enableTagOptions: true});
    });






    //need to bind showand hide container events 
//      $('fieldset.collapsible').bind('show',function (event,ui){
//          plot.replot();
//      });


    // dynamic ajax plot 
    // data array contain 2 keys : data and options
    // options is jqplot options

    $jq('div.plot[src]').each(function () {
        var url = '?q=' + $jq(this).attr('src');
        var ajaxData = function () {
            var ret = null;
            $jq.ajax({
                async: false,
                url: url,
                dataType: "json",
                success: function (data) {
                    ret = data;
                },
                error: function (err, status, msg) {
                    alert(msg + ' (' + status + ')');
                }
            });

            return ret;
        };
        var plot = ajaxData();
        if (plot) {
            $jq.jqplot.config.enablePlugins = true;
            var options = plot['options'];
            // valuta i plugin
            try {
                options.seriesDefaults.renderer = eval(options.seriesDefaults.renderer);
            } catch (error) {
            }
            try {
                options.axesDefaults.labelRenderer = eval(options.axesDefaults.labelRenderer);
            } catch (error) {
            }
            try {
                options.axes.xaxis.renderer = eval(options.axes.xaxis.renderer);
            } catch (error) {
            }
            try {
                options.axes.yaxis.renderer = eval(options.axes.yaxis.renderer);
            } catch (error) {
            }
            try {
                options.legend.renderer = eval(options.legend.renderer);
            } catch (error) {
            }
            //mancano alcuni plugin...


            var obj = plot['data'];
            var dt = []
            for (var j in obj) {
                dt[j] = obj[j]
            }
            //check id contenitore
            var id = $jq(this).attr('id');
            if (typeof id === typeof undefined || id === false) {
                //set random id 
                id = new Array(5).join().replace(/(.|$)/g, function () {
                    return ((Math.random() * 36) | 0).toString(36);
                })
                $jq(this).attr('id', id)
            }
            $jq.jqplot(id, dt, options);
        }
    });

//  BEGIN  --- impleentazione statica da eliminare progressivamente  
    var plots = Drupal.settings.plots;
    $jq.jqplot.config.enablePlugins = true;
    for (var i in plots) {
        var options = plots[i]['settings'];
        // valuta i plugin
        try {
            options.seriesDefaults.renderer = eval(options.seriesDefaults.renderer);
        } catch (error) {
        }
        try {
            options.axesDefaults.labelRenderer = eval(options.axesDefaults.labelRenderer);
        } catch (error) {
        }
        try {
            options.axes.xaxis.renderer = eval(options.axes.xaxis.renderer);
        } catch (error) {
        }
        try {
            options.axes.yaxis.renderer = eval(options.axes.yaxis.renderer);
        } catch (error) {
        }
        try {
            options.legend.renderer = eval(options.legend.renderer);
        } catch (error) {
        }
        //mancano alcuni plugin...


        var obj = plots[i]['data'];
        var dt = []
        for (var j in obj) {
            dt[j] = obj[j]
        }

        $jq.jqplot(i, dt, options);
    }
    //  END  --- impleentazione statica da eliminare progressivamente  
};// end Drupal.behaviours...

