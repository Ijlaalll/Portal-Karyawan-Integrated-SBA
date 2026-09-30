"""
Active Directory LDAP Authentication Helper
PT Semen Indonesia (SIG) - Portal Karyawan Integrated SBA

Usage from command line:
    python scripts/ldap_auth.py <username> <password>

Returns JSON:
    {"success": true, "data": {...}}
    or
    {"success": false, "error": "error message"}
"""

import sys
import os
import json

def ldap_authenticate(username: str, password: str, host: str = None, port: int = None, domain: str = None, base_dn: str = None) -> dict:
    host = host or os.getenv('LDAP_HOST', '10.60.12.44')
    port = int(port or os.getenv('LDAP_PORT', '389'))
    domain = domain or os.getenv('LDAP_DOMAIN', 'sig.id')
    base_dn = base_dn or os.getenv('LDAP_BASE_DN', '')

    try:
        from ldap3 import Server, Connection, ALL, SIMPLE
        from ldap3.core.exceptions import LDAPException
    except ImportError:
        return {
            "success": False,
            "error": "Library 'ldap3' tidak terinstall. Jalankan: pip install ldap3"
        }

    try:
        user_upn = username if '@' in username else f"{username}@{domain}"
        clean_user = username.split('@')[0]
        
        server = Server(host, port=port, get_info=ALL, connect_timeout=3)
        conn = Connection(server, user=user_upn, password=password, authentication=SIMPLE, auto_bind=True)

        if not conn.bound:
            return {
                "success": False,
                "error": "Gagal mengikat (bind) ke Active Directory LDAP"
            }

        search_base = base_dn if base_dn else f"DC={domain.replace('.', ',DC=')}"
        search_filter = f"(&(objectClass=user)(|(sAMAccountName={clean_user})(userPrincipalName={user_upn})))"

        user_info = {
            "username": clean_user,
            "email": user_upn,
            "name": clean_user,
            "department": None,
            "position": None,
            "employee_id": None
        }

        # Try to query additional user attributes
        try:
            if conn.search(search_base, search_filter, attributes=['displayName', 'mail', 'department', 'title', 'employeeID']):
                if conn.entries:
                    entry = conn.entries[0]
                    if hasattr(entry, 'displayName') and entry.displayName.value:
                        user_info["name"] = str(entry.displayName.value)
                    if hasattr(entry, 'mail') and entry.mail.value:
                        user_info["email"] = str(entry.mail.value)
                    if hasattr(entry, 'department') and entry.department.value:
                        user_info["department"] = str(entry.department.value)
                    if hasattr(entry, 'title') and entry.title.value:
                        user_info["position"] = str(entry.title.value)
                    if hasattr(entry, 'employeeID') and entry.employeeID.value:
                        user_info["employee_id"] = str(entry.employeeID.value)
        except Exception:
            pass  # Fall back to basic user info

        conn.unbind()
        return {
            "success": True,
            "data": user_info
        }

    except Exception as e:
        return {
            "success": False,
            "error": f"[LDAP Error]: {str(e)}"
        }

if __name__ == '__main__':
    if len(sys.argv) < 3:
        print(json.dumps({"success": False, "error": "Argumen kurang: python ldap_auth.py <username> <password>"}))
        sys.exit(1)

    username_arg = sys.argv[1]
    password_arg = sys.argv[2]
    
    res = ldap_authenticate(username_arg, password_arg)
    print(json.dumps(res))
