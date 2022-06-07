from src.client.client_factory import ClientFactory

class PlatformService:
    def get_list(self):
        client = ClientFactory.get_read_client()

        return client.get('platforms?orderBy[]=name-asc&limit=50').json()['result']
