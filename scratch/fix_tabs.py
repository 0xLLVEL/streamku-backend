import re
import sys

def fix_tabs(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find all occurrences of `{activeTab === "..." && (` or `{["...", "..."].includes(activeTab) && (`
    # We will just replace it with `<div className={activeTab === "..." ? "block" : "hidden"}>`
    
    # regex for {activeTab === "..." && (
    content = re.sub(r'\{activeTab === "(.*?)" && \(', r'<div className={activeTab === "\1" ? "block" : "hidden"}>', content)
    
    # regex for {["...", "..."].includes(activeTab) && (
    content = re.sub(r'\{\["(.*?)", "(.*?)"\]\.includes\(activeTab\) && \(', r'<div className={["\1", "\2"].includes(activeTab) ? "block" : "hidden"}>', content)

    # Now we need to replace the corresponding `)}` with `</div>`.
    # A simple way: since all these blocks end right before `          {/*` or `        </div>`
    # we can replace `\n          \)\}\n\n          {/\*` with `\n          </div>\n\n          {/*`
    
    content = re.sub(r'          \)\}\n\n          {/\*', r'          </div>\n\n          {/*', content)
    
    # and the last one ends before `        </div>\n      </div>\n    </form>`
    content = re.sub(r'          \)\}\n        </div>\n      </div>', r'          </div>\n        </div>\n      </div>', content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

fix_tabs('client/components/admin/TvShowEditForm.tsx')
fix_tabs('client/components/admin/MovieEditForm.tsx')
print("Done")
