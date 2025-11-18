# Konfigurasi PyMySQL untuk Django
import pymysql

pymysql.install_as_MySQLdb()

# Workaround untuk MariaDB 10.4 (menonaktifkan pengecekan versi dan fitur RETURNING)
# HATI-HATI: Ini hanya untuk development, tidak disarankan untuk production
import django.db.backends.mysql.base as mysql_base
import django.db.backends.mysql.features as mysql_features
import django.db.models.sql.compiler as sql_compiler
import django.db.models.base as models_base
import re

# Simpan method asli
_original_check_database_version_supported = mysql_base.DatabaseWrapper.check_database_version_supported

# Override method untuk menonaktifkan pengecekan versi
def check_database_version_supported(self):
    # Skip pengecekan versi untuk development
    pass

# Terapkan override
mysql_base.DatabaseWrapper.check_database_version_supported = check_database_version_supported

# Nonaktifkan fitur RETURNING dengan memodifikasi SQL compiler dan model base
_original_execute_sql = sql_compiler.SQLInsertCompiler.execute_sql

def execute_sql(self, returning_fields=None):
    # Selalu set returning_fields ke None untuk menghindari RETURNING clause
    result = _original_execute_sql(self, returning_fields=None)
    return result

sql_compiler.SQLInsertCompiler.execute_sql = execute_sql

# Modifikasi _do_insert untuk tidak menggunakan RETURNING
_original_do_insert = models_base.Model._do_insert

def _do_insert(self, manager, using, fields, returning_fields, raw):
    # Selalu set returning_fields ke None untuk MariaDB 10.4
    # Hanya untuk operasi insert, bukan update
    result = _original_do_insert(self, manager, using, fields, None, raw)
    # Jika user belum punya PK setelah insert, dapatkan dari database menggunakan LAST_INSERT_ID()
    if self.pk is None:
        from django.db import connections
        db = connections[using] if using else connections['default']
        with db.cursor() as cursor:
            cursor.execute("SELECT LAST_INSERT_ID()")
            last_id = cursor.fetchone()[0]
            if last_id:
                self.pk = last_id
    return result

models_base.Model._do_insert = _do_insert

# Modifikasi _save_table untuk menangani kasus update dengan update_fields
# Hapus workaround ini karena menyebabkan konflik dengan parameter yang sudah ada
# Workaround di _do_insert sudah cukup untuk menangani RETURNING clause

# Modifikasi bulk_create untuk tidak menggunakan RETURNING
import django.db.models.query as query_module

# Patch assertion di bulk_create untuk menangani kasus tanpa RETURNING
_original_bulk_create = query_module.QuerySet.bulk_create

def bulk_create(self, objs, batch_size=None, ignore_conflicts=False, update_conflicts=False, update_fields=None, unique_fields=None):
    # Untuk MariaDB 10.4 yang tidak mendukung RETURNING, gunakan fallback
    # Insert satu per satu jika ada objek tanpa PK
    if not objs:
        return []
    
    from django.db import connections
    db_alias = self.db if isinstance(self.db, str) else (self.db.alias if hasattr(self.db, 'alias') else 'default')
    
    # Cek apakah ada objek tanpa PK - jika ada, gunakan fallback untuk MariaDB 10.4
    objs_without_pk = [obj for obj in objs if obj.pk is None]
    if objs_without_pk:
        # Untuk MariaDB 10.4, selalu gunakan fallback (insert satu per satu)
        # karena RETURNING tidak didukung
        results = []
        for obj in objs:
            obj.save(using=db_alias)
            results.append(obj)
        return results
    
    # Jika semua objek sudah punya PK, gunakan metode normal
    return _original_bulk_create(self, objs, batch_size=batch_size, ignore_conflicts=ignore_conflicts, 
                                 update_conflicts=update_conflicts, update_fields=update_fields, unique_fields=unique_fields)

query_module.QuerySet.bulk_create = bulk_create

# Override as_sql untuk menghapus RETURNING clause
_original_as_sql = sql_compiler.SQLInsertCompiler.as_sql

def as_sql(self, *args, **kwargs):
    result = _original_as_sql(self, *args, **kwargs)
    # Handle both tuple and list returns
    if isinstance(result, tuple) and len(result) == 2:
        sql, params = result
        # Hapus RETURNING clause dari SQL
        if isinstance(sql, (list, tuple)):
            sql = [re.sub(r'\s+RETURNING\s+[^;]+', '', str(s), flags=re.IGNORECASE) for s in sql]
        else:
            sql = re.sub(r'\s+RETURNING\s+[^;]+', '', str(sql), flags=re.IGNORECASE)
        return sql, params
    elif isinstance(result, list):
        # Handle list of tuples
        return [(re.sub(r'\s+RETURNING\s+[^;]+', '', str(sql), flags=re.IGNORECASE), params) 
                for sql, params in result]
    return result

sql_compiler.SQLInsertCompiler.as_sql = as_sql

# Juga nonaktifkan pada features
_original_get_new_connection = mysql_base.DatabaseWrapper.get_new_connection

def get_new_connection(self, conn_params):
    conn = _original_get_new_connection(self, conn_params)
    # Nonaktifkan fitur RETURNING pada instance features
    if hasattr(conn, 'features'):
        # Set langsung sebagai attribute (bukan property)
        object.__setattr__(conn.features, 'supports_returning_columns', False)
        object.__setattr__(conn.features, 'can_return_rows_from_bulk_insert', False)
    return conn

mysql_base.DatabaseWrapper.get_new_connection = get_new_connection

