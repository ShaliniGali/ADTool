import pytest
import optimizer.alignment as test


def test_calculate_jca_manual_override(mock_session):
    id = 3632
    result = test.calculate_jca_manual_override(str(id),mock_session)
    assert result.value['selected_programs'] is not None


def test_calculate_cga_manual_override(mock_session):
    id = 3632
    result = test.calculate_cga_manual_override(str(id),mock_session)
    assert result.value['selected_programs'] is not None


def test_calculate_kp_manual_override(mock_session):
    id = 3632
    result = test.calculate_kp_manual_override(str(id),mock_session)
    assert result.value['selected_programs'] is not None    