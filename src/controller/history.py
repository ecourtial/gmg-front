""" History controller for the GMG project """
from flask import jsonify, request, render_template, session
from src.repository.history_repository import HistoryRepository
from src.service.history_service import HistoryService

class HistoryController:
    """ History controller for the GMG project """
    @classmethod
    def get_list(cls):
        """Return the whole history."""
        service = HistoryService()

        return jsonify(
            games=service.get_history()
        )

    @classmethod
    def add(cls, mysql):
        """Add a new history."""
        if request.method == 'GET':
            form = render_template(
                'general/history-form.html',
                token=session['csrfToken']
            )

            return jsonify(form=form, title="Ajouter un jeu")

        if request.form['_token'] != session['csrfToken']:
            return jsonify(), 400

        game_id = request.form['game_id']
        year = request.form['year']
        position = request.form['position']
        watched = request.form['watched']
        played = request.form['played']
        if game_id == '' or year == '' or position == '' or watched == '' or played == '':
            return "Form is incomplete"

        history_repo = HistoryRepository(mysql)
        history_repo.insert(game_id, year, position, watched, played)

        return jsonify(), 200

    @classmethod
    def delete(cls, mysql, entry_id):
        """Delete a history entry."""
        if request.form['_token'] != session['csrfToken']:
            return jsonify(), 400

        history_repo = HistoryRepository(mysql)
        history_repo.delete(entry_id)

        return jsonify(), 204
