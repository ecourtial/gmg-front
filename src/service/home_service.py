from src.client.client_factory import ClientFactory

class HomeService:
    def get_home_data(self):
        client = ClientFactory.get_read_client()

        to_do_count = client.get('versions?toDo[]=1&page=1&limit=1').json()['totalResultCount']
        to_watch_background_count = client.get('versions?toWatchBackground[]=1&page=1&limit=1').json()['totalResultCount']
        to_watch_serious_count = client.get('versions?toWatchSerious[]=1&page=1&limit=1').json()['totalResultCount']

        return {
            'game_count': client.get('games?page=1&limit=1').json()['totalResultCount'],
            'versionCount': client.get('versions?page=1&limit=1').json()['totalResultCount'],
            'versionFinishedCount': client.get('versions?finished[]=1&page=1&limit=1').json()['totalResultCount'],
            'ownedGameCount': client.get('versions?copyCount[]=neq-0&limit=1').json()['totalResultCount'],
            'platformCount': client.get('platforms?page=1&limit=1').json()['totalResultCount'],
            'toDoSoloOrToWatch': to_do_count + to_watch_background_count + to_watch_serious_count,
            'hallOfFameGames': client.get('versions?hallOfFame[]=1&hallOfFameYear[]=neq-0&hallOfFamePosition[]=neq-0&orderBy[]=hallOfFameYear-asc&orderBy[]=hallOfFamePosition-asc&limit=200').json()['result'],
        }
