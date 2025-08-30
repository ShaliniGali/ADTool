import pandas as pd
from sqlalchemy import text

class DTTableValidator:
    @staticmethod
    def validate_integer_columns(df, int_columns):
        for col in int_columns:
            if col in df.columns:
                if not pd.to_numeric(df[col], errors='coerce').notna().all():
                    print(f" Error: Column '{col}' contains non-integer values.")
                    return False
        return True

    @staticmethod
    def validate_string_columns(df, str_columns, max_length):
        for col in str_columns:
            if col in df.columns:
                if not df[col].astype(str).apply(lambda x: len(x) <= max_length).all():
                    print(f" Error: Column '{col}' exceeds {max_length} characters.")
                    return False
        return True

    @staticmethod
    def validate_required_columns(df, required_columns):
        missing_columns = [col for col in required_columns if col not in df.columns]
        if missing_columns:
            print(f" Error: Missing required columns: {missing_columns}")
            return False
        return True

    @staticmethod
    def validate_unique_rows(df, unique_columns):
        if df.duplicated(subset=unique_columns).any():
            print(f" Error: Duplicate rows found based on columns: {unique_columns}")
            return False
        return True

    @staticmethod
    def validate_missing_values(df, required_columns):
        for col in required_columns:
            if col in df.columns and df[col].isna().sum() > 0:
                print(f" Error: Column '{col}' contains missing values.")
                return False
        return True

    @classmethod
    def validate_all(cls, df, int_columns, str_columns, max_length, required_columns, unique_columns):
        return (
            cls.validate_integer_columns(df, int_columns) and
            cls.validate_string_columns(df, str_columns, max_length) and
            cls.validate_required_columns(df, required_columns) and
            cls.validate_unique_rows(df, unique_columns) and
            cls.validate_missing_values(df, required_columns)
        )

    @staticmethod
    def get_columns_from_db(session, table_name, schema_name):
        query = text("""
            SELECT COLUMN_NAME
            FROM information_schema.columns
            WHERE table_name = :table_name AND table_schema = :schema_name;
        """)
        result = session.execute(query, {"table_name": table_name, "schema_name": schema_name}).fetchall()
        return [row[0] for row in result] if result else []
