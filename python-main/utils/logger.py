import sys
import logging


date_format_str = '%Y-%m-%d %H:%M:%S'

def get_logger(name):
    logging.basicConfig(stream=sys.stdout, format='[%(asctime)s] %(levelname)s - %(message)s', datefmt=date_format_str)
    app_logger = logging.getLogger(name)
    app_logger.setLevel(level=logging.DEBUG)

    return app_logger