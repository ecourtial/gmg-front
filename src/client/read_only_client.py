from src.client.client import ApiClient

class ReadOnlyClient(ApiClient):

    def get(cls, endpoint, headers = None):
        return super().api_call('get', endpoint, headers = headers)
