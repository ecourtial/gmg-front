import random
from src.client.client_factory import ClientFactory

class GameService:
    max_result_limit = 500

    authorized_fields = [
        'bgf',
        'singleplayer_recurring',
        'multiplayer_recurring',
        'todo_solo_sometimes',
        'todo_multiplayer_sometimes',
        'originals',
        'ongoing',
        'to_do',
        'to_watch_background',
        'to_watch_serious',
        'to_buy',
    ]

    random_cases = [
        'singleplayer_random',
        'multiplayer_random'
    ]

    def get_random_game(self, filter):
        if filter == 'singleplayer_random':
            filter_string = random.choice(['todoSoloSometimes', 'singleplayerRecurring', 'toDo'])
        elif filter == 'multiplayer_random':
            filter_string = random.choice(['todoMultiplayerSometimes', 'multiplayerRecurring'])
        else:
            return None

        client = ClientFactory.get_read_client()
        result = client.get(f"versions?{filter_string + '[]=1'}&orderBy[]=rand&limit=1").json()

        if result['resultCount'] == 0:
            return None
        else:
            return result['result'][0]

    def get_special_list(self, filter):
        if filter == 'bgf':
            request = 'versions?bestGameForever[]=1&orderBy[]=gameTitle-asc&limit=' + str(self.max_result_limit)
        elif filter == 'singleplayer_recurring':
            request = 'versions?singleplayerRecurring[]=1&orderBy[]=gameTitle-asc&limit=' + str(self.max_result_limit)
        elif filter == 'multiplayer_recurring':
            request = 'versions?multiplayerRecurring[]=1&orderBy[]=gameTitle-asc&limit=' + str(self.max_result_limit)
        elif filter == 'todo_solo_sometimes':
            request = 'versions?todoSoloSometimes[]=1&orderBy[]=gameTitle-asc&limit=' + str(self.max_result_limit)
        elif filter == 'todo_multiplayer_sometimes':
            request = 'versions?todoMultiplayerSometimes[]=1&orderBy[]=gameTitle-asc&limit=' + str(self.max_result_limit)
        elif filter == 'ongoing':
            request = 'versions?ongoing[]=1&orderBy[]=gameTitle-asc&limit=' + str(self.max_result_limit)
        elif filter == 'to_do':
            request = 'versions?toDo[]=1&orderBy[]=gameTitle-asc&limit=' + str(self.max_result_limit)
        elif filter == 'to_watch_background':
            request = 'versions?toWatchBackground[]=1&orderBy[]=gameTitle-asc&limit=' + str(self.max_result_limit)
        elif filter == 'to_watch_serious':
            request = 'versions?toWatchSerious[]=1&orderBy[]=gameTitle-asc&limit=' + str(self.max_result_limit)
        elif filter == 'to_buy':
            request = 'versions?toBuy[]=1&orderBy[]=gameTitle-asc&limit=' + str(self.max_result_limit)
        elif filter == 'originals':
            return self.get_originals()
        else:
            return {}

        return self.get_list(request)

    def get_list(self, request):
        client = ClientFactory.get_read_client()
        result = client.get(request).json()

        if result['resultCount'] == 0:
            return {}
        else:
            return result['result']

    def get_originals(self):
        client = ClientFactory.get_read_client()
        result = client.get('copies?original=1&limit=' + str(self.max_result_limit)).json()['result']
        copies_id = []

        for copy in result:
            if copy['original'] is True and copy['versionId'] not in copies_id:
                copies_id.append(copy['versionId'])

        query = ''
        for version_id in copies_id:
            query += '&id[]=' + str(version_id)

        return client.get('versions?orderBy[]=gameTitle-asc&limit=' + str(self.max_result_limit) + query).json()['result']
