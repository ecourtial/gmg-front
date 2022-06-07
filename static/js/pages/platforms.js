/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 */
define(
    ["jquery", "tools"],
    function ($, tools) {
        "use strict";

        return {
            /**
             * Diplay the list of platforms
             */
            diplayData: function (data, context) {
                $('#contentTitle').html('Par support');
                var content = '<ul>';

                $.each(data.platforms, function (index, value) {
                    content += '<li>' + tools.filterContent(value.name) + ' (' + value.versionCount + ') - <a data-link-type="gamePerPlatform" id="entryL' + tools.filterContent(value.id) + '" href="">Voir la liste</a></li>'
                });

                content += '</ul>';

                $('#content').empty().html(content);
            }
        };
    }
);
