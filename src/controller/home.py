""" Home controller for the GMG project """

from flask import render_template, jsonify, session
from src.service.home_service import HomeService

class HomeController:
    """ Contains two methods: one for serving the website, one for the homepage content (async) """
    @classmethod
    def get_app_content(cls):
        """Return the html content (layout)."""
        return render_template(
            'layout.html',
            token=session.get('csrfToken', ''),
            show_menu=True,
            title="Give me a game", content_title="Hall of fames"
        )

    @classmethod
    def get_home_content(cls):
        """Return The payload for the homepage."""
        service = HomeService()
        data = service.get_home_data()

        return jsonify(
            gameCount=data['game_count'],
            versionCount=data['versionCount'],
            versionFinishedCount = data['versionFinishedCount'],
            platformCount=data['platformCount'],
            ownedGameCount=data['ownedGameCount'],
            toDoSoloOrToWatch=data['toDoSoloOrToWatch'],
            hallOfFameGames=data['hallOfFameGames']
        )
