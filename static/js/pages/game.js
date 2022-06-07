/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 */
define(
    ["jquery", "tools"],
    function ($, tools) {
        "use strict";

        return {
            /**
             * Diplay the details of a game
             */
            diplayData: function (data, context) {
                $('#contentTitle').html(tools.filterContent(data.game.gameTitle) + ' (' + tools.filterContent(data.game.platformName) + ')');
                var content = $('#gameDetailContent').html();

                // Badges and links
                var badges = this.getBadges(data.game);
                if (badges === '') {
                    badges = 'Aucun';
                }
                content = content.replace("@BADGES@", badges);
                
                if (logged) {
                    var editLink = '<p> <a data-link-type="gameEdit" id="entryE' + tools.filterContent(data.game.id) + '" href="">Editer</a></p>';
                    var deleteLink = '<p> <a data-link-type="gameDelete" id="entryR' + tools.filterContent(data.game.id) + '" href="">Supprimer</a></p>';
                    content = editLink + deleteLink + content;
                }

                content = '<p><a href="" onClick="return dispatchReturnToListEvent(' + tools.filterContent(data.game.platformId) 
                    + ')" id="returnToPrevious">Retourner à la liste des jeux <strong>' + tools.filterContent(data.game.platformName) + '</strong></a></p>' + content;

                // Main content
                content = content.replace("@ID@", tools.filterContent(data.game.id));
                content = content.replace("@RELEASE_YEAR@", tools.filterContent(this.getReleaseYear(data.game.releaseYear)));
                content = content.replace("@IS_SOLO_RECCURING@", this.boolToYesNoConverter(data.game.singleplayerRecurring));
                content = content.replace("@IS_MULTI_RECCURING@", this.boolToYesNoConverter(data.game.multiplayerRecurring));
                content = content.replace("@IS_TO_DO@", this.boolToYesNoConverter(data.game.toDo));
                content = content.replace("@IS_TO_PLAY_SOLO_SOMETIMES@", this.boolToYesNoConverter(data.game.todoSoloSometimes));
                content = content.replace("@IS_TO_PLAY_MULTI_SOMETIMES@", this.boolToYesNoConverter(data.game.todoMultiplayerSometimes));
                content = content.replace("@IS_TO_BUY@", this.boolToYesNoConverter(data.game.toBuy));
                content = content.replace("@IS_TO_WATCH_BACKGROUND@", this.boolToYesNoConverter(data.game.toWatchBackground));
                content = content.replace("@IS_TO_WATCH_SERIOUS@", this.boolToYesNoConverter(data.game.toWatchSerious));
                content = content.replace("@IS_TO_WATCH_AGAIN@", this.boolToYesNoConverter(data.game.toRewatch));
                content = content.replace("@IS_TOP@", this.boolToYesNoConverter(data.game.topGame));
                content = content.replace("@IS_PLAYED_OFTEN@", this.boolToYesNoConverter(data.game.playedItOften));
                content = content.replace("@ONGOING@", this.boolToYesNoConverter(data.game.ongoing));

                // Hall of fame
                if (data.game.hallOfFame === 0) {
                    var hofYear = 'N/A';
                    var hofPosition = hallOfFameYear;
                } else {
                    var hofYear = data.game.hallOfFameYear;
                    var hofPosition = data.game.hallOfFamePosition;
                }
                content = content.replace("@HALL_YEAR@", tools.filterContent(hofYear));
                content = content.replace("@HALL_POSITION@", tools.filterContent(hofPosition));
                
                // Comments
                if (data.game.comments === '' || data.game.comments === null) {
                    var comments = 'Aucun';
                } else {
                    var comments = data.game.comments;
                    comments = tools.filterContent(comments);
                    comments = comments.replace(/[\n\r]/g, '<br/>');
                }
                content = content.replace("@COMMENTS@", comments);

                $('#content').empty().html(content);
            },

            boolToYesNoConverter: function (value) {
                return value === 1 ? "Oui" : "Non";
            },

            getReleaseYear: function(value) {
                if (value == 0) {
                    return 'Non renseignée';
                }

                return value;
            },

            getBadges: function(value) {
                var gameEntry = '';

                if (value.copyCount !== 0) {
                    gameEntry += '<img title="Je possède une version" src="' + checkImageUrl + '"/>'
                } else {
                    gameEntry += '<img title="Je ne possède aucune version" src="' + noImageUrl + '"/>'
                }

                if (value.hallOfFame === 1) {
                    gameEntry += ' <img title="Dans le hall of fame" src="' + hallOfFameImageUrl + '"/>'
                }

                if (value.bgf === 1) {
                    gameEntry += ' <img title="Membre des Best Games Forever" src="' + diamondImageUrl + '"/>'
                }

                if (value.topGame === 1) {
                    gameEntry += ' <img title="Top jeu" src="' + topImageUrl + '"/>'
                }

                if (value.playedItOften === 1) {
                    gameEntry += ' <img title="Beaucoup joué" src="' + playedOftenImageUrl + '"/>'
                }

                if (value.toBuy === 1) {
                    gameEntry += ' <img title="À acheter" src="' + toBuyImageUrl + '"/>'
                }

                if (value.toDo == 1 && value.todo_with_help === 1) {
                    gameEntry += ' <img title="À faire avec aide ou solution" src="' + withHelpImageUrl + '"/>'
                }

                return gameEntry;
            }
        };
    }
);
