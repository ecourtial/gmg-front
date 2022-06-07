""" Platforms controller for the GMG project """
from flask import jsonify, request, render_template, session
from src.repository.platform_repository import PlatformRepository
from src.service.platform_service import PlatformService

class PlatformController:
    """ Platforms controller for the GMG project """
    @classmethod
    def get_list(cls):
        """Return the platform list."""
        service = PlatformService()

        return jsonify(platforms=service.get_list())

    @classmethod
    def add(cls, mysql):
        """Add a new platform."""
        if request.method == 'GET':
            return render_template(
                'general/platform-form.html',
                show_menu=True,
                content_title="Ajouter une plateforme", token=session['csrfToken']
            )

        if request.form['_token'] != session['csrfToken']:
            return jsonify(), 400

        name = request.form['platform_name']
        if name == '':
            return "Form is incomplete"

        repo = PlatformRepository(mysql)

        return jsonify(id=repo.insert(name))
