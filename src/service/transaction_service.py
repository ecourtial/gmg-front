from src.client.client_factory import ClientFactory

class TransactionService:
    max_result_limit = 500

    def get_history(self):
        client = ClientFactory.get_read_client()

        return client.get('transactions?type[]=Bought&type[]=Sold&orderBy[]=year-asc&orderBy[]=month-asc&orderBy[]=day-asc&limit=' + str(self.max_result_limit)).json()['result']
