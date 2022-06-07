""" Games controller for the GMG project """
from flask import jsonify, request, render_template, session
from src.service.game_service import GameService
from src.repository.game_repository import GameRepository
from src.repository.platform_repository import PlatformRepository

class GameController:
    """ Games controller for the GMG project """
    @classmethod
    def get_list_by_platform(cls, mysql, platform_id):
        """Return the platform list."""
        game_repo = GameRepository(mysql)
        games_list = game_repo.get_list_by_platform(platform_id)
        platform_repo = PlatformRepository(mysql)
        platform = platform_repo.get_by_id(platform_id)
        return jsonify(
            platform=platform.serialize(), games=[game.serialize() for game in games_list]
        )


    @classmethod
    def get_by_id(cls, mysql, game_id):
        """Return the platform list."""
        game_repo = GameRepository(mysql)
        game = game_repo.get_by_id(game_id)

        if game is None:
            return jsonify(message='Unknown game with id: #' + str(game_id)), 404

        return jsonify(game=game.serialize())

    @classmethod
    def get_random(cls, random_filter):
        """Return a random game."""
        if random_filter not in GameService.random_cases:
            return jsonify(message='Unknown random filter: ' + random_filter), 404

        game_service = GameService()
        game = game_service.get_random_game(random_filter)

        if game is None:
            return jsonify(message='No result with filter ' + random_filter)

        return jsonify(game=game)

    @classmethod
    def get_special_list(cls, special_filter):
        """Various lists of games"""

        if special_filter == 'search':
            query = request.args.get('query')
            #games_list = game_repo.get_search(query)
        elif special_filter not in GameService.authorized_fields:
            return jsonify('Unknown filter: ' + special_filter), 404
        else:
            game_service = GameService()
            games_list = game_service.get_special_list(special_filter)

        return jsonify(games=games_list)


    @classmethod
    def add(cls, mysql):
        """Add a new game."""
        if request.method == 'GET':
            platform_repo = PlatformRepository(mysql)
            form = render_template(
                'general/game-form.html',
                token=session['csrfToken'],
                platforms=platform_repo.get_list()
            )

            return jsonify(form=form, title="Ajouter un jeu")

        if request.form['_token'] != session['csrfToken']:
            return jsonify(), 400

        title = request.form['title']
        platform_id = request.form['platform']
        if title == '' or platform_id == '':
            return "Form is incomplete"

        platform_repo = PlatformRepository(mysql)
        platform = platform_repo.get_by_id(platform_id)
        if platform is None:
            return "Invalid platform"

        game_repo = GameRepository(mysql)

        return jsonify(id=game_repo.insert(title, platform_id, request.form))

    @classmethod
    def edit(cls, mysql, game_id):
        """Edit a game."""
        if request.method == 'GET':
            game_repo = GameRepository(mysql)
            game = game_repo.get_by_id(game_id)

            if game is None:
                return jsonify(), 404

            platform_repo = PlatformRepository(mysql)

            form = render_template(
                'general/game-form.html',
                token=session['csrfToken'],
                platforms=platform_repo.get_list(),
                game=game
            )

            return jsonify(form=form, title="Editer '" + game.get_title() + "'")

        if request.form['_token'] != session['csrfToken']:
            return jsonify(), 400

        title = request.form['title']
        platform_id = request.form['platform']
        if title == '' or platform_id == '':
            return "Form is incomplete"

        platform_repo = PlatformRepository(mysql)
        platform = platform_repo.get_by_id(platform_id)
        if platform is None:
            return "Invalid platform"

        game_repo = GameRepository(mysql)

        return jsonify(id=game_repo.update(game_id, title, platform_id, request.form))

    @classmethod
    def delete(cls, mysql, game_id):
        """Delete a game."""
        if request.form['_token'] != session['csrfToken']:
            return jsonify(), 400

        game_repo = GameRepository(mysql)
        game_repo.delete(game_id)

        return jsonify(), 204
