import pytest
import rds.table_model.socom.program_tables as test
from api.internal import socom_models
import pandas as pd

from fastapi import HTTPException
from decimal import Decimal


def test_get_total_score_from_progId(mock_session):
    program_ids = ["OTHERII_NSW_NSW", "OTHERIZ_AFSOC_NSW"]
    LookupStorm = test.LookupProgramModel
    result = test.LookupStorm.get_total_score_from_progIds(LookupStorm, mock_session, program_ids, to_dict=False)
    expected = [('OTHERII_NSW_NSW', 49), ('OTHERIZ_AFSOC_NSW', 73)]
    assert result == expected
    
    result = test.LookupStorm.get_total_score_from_progIds(LookupStorm, mock_session, program_ids, to_dict=True)
    expected = {'OTHERII_NSW_NSW': 49, 'OTHERIZ_AFSOC_NSW': 73}
    for k in expected.keys():
        assert result[k] == expected[k]
        
    assert len(result) == len(expected)
    