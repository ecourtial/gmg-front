from src.client.client import ApiClient
from src.client.read_only_client import ReadOnlyClient

class ClientFactory:
    @classmethod
    def init(cls, back_end_url, read_only_token):
        cls.back_end_url = back_end_url
        cls.read_only_token = read_only_token
        cls.read_only_client = None

    @classmethod
    def get_read_client(cls):
        if cls.read_only_client is None:
            cls.read_only_client = ReadOnlyClient(cls.back_end_url, cls.read_only_token)
        
        return cls.read_only_client
