import requests
import json

class ApiClient:

    def __init__(cls, back_end_url, token):
        cls.token = token
        cls.back_end_url = back_end_url

    def api_call(cls, method, endpoint, payload = None, headers = None):
        method_to_call = getattr(requests, method)

        payload = {} if payload is None else json.dumps(payload)
        headers = {} if headers is None else headers

        headers['Authorization'] = 'token ' + cls.token
        headers['Content-Type'] = 'application/json'

        return method_to_call(url=cls.back_end_url + endpoint, data = payload, headers = headers)
