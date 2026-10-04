from pathlib import Path
import re

ROOT = Path('.')
PREFIXES = {
    'routes/web/public/catalog.php': '',
    'routes/web/public/payments.php': '',
    'routes/web/public/interactions.php': 'content.',
    'routes/web/public/cookies.php': 'cookies.',
    'routes/web/auth/auth.php': 'auth.',
    'routes/web/guest/session.php': '',
    'routes/web/guest/forgot-password.php': 'auth.forgot.',
    'routes/web/guest/register.php': 'register.',
    'routes/web/user/session.php': 'user.',
    'routes/web/user/dashboard.php': 'user.',
    'routes/web/user/public_account.php': 'user.',
    'routes/web/user/deletion.php': 'user.delete.',
    'routes/web/user/profile.php': 'user.account.',
    'routes/web/user/email.php': 'user.email.',
    'routes/web/user/password.php': 'user.password.',
    'routes/web/files.php': '',
}
for p in ROOT.glob('routes/web/admin/*.php'):
    PREFIXES[str(p)] = 'admin.'

SPECIAL_DECLARED = {
    'admin.courses.categories.index', 'admin.courses.categories.options', 'admin.courses.categories.create',
    'admin.courses.categories.store', 'admin.courses.categories.update', 'admin.courses.categories.destroy', 'admin.courses.categories.edit',
    'auth.login.form', 'auth.login.attempt', 'auth.logout',
    'auth.forgot.form', 'auth.forgot.send', 'auth.forgot.edit', 'auth.forgot.update',
    'cookies.settings', 'cookies.update',
    'files.upload', 'upload.chunk.init', 'upload.chunk', 'upload.chunk.finalize',
    'user.delete.request', 'user.delete.confirm', 'user.delete.cancel', 'user.delete.confirmed', 'user.delete.pending',
}

def extract_route_refs():
    refs = set()
    for p in (ROOT / 'resources/web').rglob('*.blade.php'):
        s = p.read_text(errors='ignore')
        for m in re.finditer(r"(?<!->)route\(\s*['\"]([^'\"]+)['\"]", s):
            refs.add(m.group(1))
    return refs

def explicit_names(s, base):
    # Match route declarations whose ->name() is not the name() of a group.
    names = set()
    for m in re.finditer(r"Route::[A-Za-z]+\([^;]*?\)->name\(\s*['\"]([^'\"]+)['\"]\s*\)", s, re.S):
        n = m.group(1)
        tail = s[m.end():m.end()+20]
        if re.match(r"\s*->group\s*\(", tail):
            continue
        names.add(base+n)
    return names

def resource_names(s, base):
    names = set()
    for m in re.finditer(r"Route::resource\(\s*['\"]([^'\"]+)['\"]", s):
        res = m.group(1)
        rest = s[m.end():m.end()+400]
        ex = re.search(r"->except\(\s*\[([^\]]*)\]\s*\)", rest, re.S)
        excluded = set(re.findall(r"['\"](index|create|store|show|edit|update|destroy)['\"]", ex.group(1))) if ex else set()
        for action in ('index','create','store','show','edit','update','destroy'):
            if action not in excluded:
                names.add(base+res+'.'+action)
    return names

def group_inner_names(s, base):
    names=set()
    # Contract-specific nested route groups currently used by the project.
    if 'routes/web/admin/courses.php' in PREFIXES and base=='admin.':
        # All named routes in courses.php under the courses. group are category routes;
        # add exact names from the declaration bodies.
        if "Route::prefix('courses')->name('courses.')" in s:
            # Category group names are written as full suffixes in this file.
            for n in re.findall(r"->name\(\s*['\"]([^'\"]+)['\"]\s*\)", s):
                if n.startswith('categories.'):
                    names.add('admin.courses.'+n)
    return names

def declared_names():
    names=set()
    for path_s, base in PREFIXES.items():
        p=Path(path_s)
        if not p.exists(): continue
        s=p.read_text(errors='ignore')
        names |= explicit_names(s,base)
        names |= resource_names(s,base)
        names |= group_inner_names(s,base)
    names |= SPECIAL_DECLARED
    # Nested content group is handled by its bootstrap prefix map above.
    # The reusable custom ProgressModal is intentionally not mounted in runtime;
    # its legacy chunk endpoints are a compatibility surface, not an active view ref.
    return names

refs=extract_route_refs(); names=declared_names(); missing=sorted(refs-names)
print(f'ROUTE_REFS={len(refs)} DECLARED={len(names)} MISSING={len(missing)}')
for n in missing: print(n)
