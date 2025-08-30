from socom import eoc_funding as test

def test_get_prog_eoc_funding(socom_session):
    payload = ["OTHERII_NSW_NSW"]
    result = test.get_prog_eoc_funding(socom_session, payload)
    
    eoc_code_ct = len([item['EOC_CODE'] for item in result])
    resource_category_code_ct = len([item['RESOURCE_CATEGORY_CODE'] for item in result])

    assert eoc_code_ct == 1 and resource_category_code_ct == 1
