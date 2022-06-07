from src.client.client_factory import ClientFactory

class HistoryService:
    max_result_limit = 500

    def get_history(self):
        client = ClientFactory.get_read_client()

        return client.get('stories?orderBy[]=year-asc&orderBy[]=position-asc&limit=' + str(self.max_result_limit)).json()['result']