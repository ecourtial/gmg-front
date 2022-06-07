/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 */
 define(
    ["jquery", "tools"],
    function ($, tools) {
        "use strict";

        return {
            /**
             * Diplay the list of games trading history
             */
            diplayData: function (data, context) {
                $('#contentTitle').html("Historique commercial");
                var content = '<p id="subtitle">Jeux vendus ou achetés</p>';
                var currentYear = 0;
                var currentMonth = 0;
                var that = this;

                $.each(data.games, function (index, value) {
                    var gameYear = parseInt(value.year);
                    var gameMonth = parseInt(value.month);
                    var yearString = '<strong style="color: yellow;">' + gameYear + '</strong>';

                    if (currentYear === 0) {
                        content += yearString + '<br/>';
                        currentYear = gameYear;
                    } else {
                        if (currentYear != gameYear) {
                            content += '</ul></ul>' + yearString + '<br/>';
                            currentYear = gameYear;
                            currentMonth = 0;
                        }
                    }

                    if (currentMonth === 0) {
                        currentMonth = gameMonth;
                        content += '<br/>' + tools.getMonthName(currentMonth) + '<ul>';  
                    } else {
                        if (currentMonth != gameMonth) {
                            currentMonth = gameMonth;
                            content += '</ul><br/>' + tools.getMonthName(currentMonth) + '<ul>';
                        }
                    }

                    var gameEntry = that.getBadges(value) + tools.filterContent(value.gameTitle) 
                        + "- " + tools.filterContent(value.title) + " (" + tools.filterContent(value.platformName) + ")";
                    gameEntry += ' - <a data-link-type="gameDetails" id="entryD' + tools.filterContent(value.id) + '" href="">Détails</a>';

                    if (logged) {
                        gameEntry += ' - <a data-link-type="gameEdit" id="entryE' + tools.filterContent(value.id) + '" href="">Editer</a>';
                        gameEntry += ' - <a data-link-type="gameDelete" id="entryR' + tools.filterContent(value.id) + '" href="">Supprimer</a>';
                        gameEntry += ' - <a data-link-type="tradingHistoryDelete" id="entryH' + tools.filterContent(value.id) + '" href="">Supprimer entrée historique</a>';
                    }

                    content += '<li>' + gameEntry + '</li>'
                });

                content += '</ul></ul>';

                $('#content').empty().html(content);
            },

            getBadges: function(value) {
                if (value.type === 'Bought') {
                    return  '<img title="J\'y ai vendu" src="' + outImageUrl + '"/> '
                }

                if (value.type === 'Sold') {
                    return ' <img title="Je l\'ai acheté" src="' + inImageUrl + '"/> '
                }

                return "";
            }
        };
    }
);
