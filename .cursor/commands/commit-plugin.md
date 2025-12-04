# commit-plugin

Selected plugin: @server\plugins\sh-shp-llm

# Generate Commit Message for Plugin Changes
This command analyzes the git status in the selected plugin directory and generates a commit message without adding or committing files. The generated command can be copy-pasted for manual execution.

## Step 1: Check Git Status in Plugin Directory
<xai:function_call name="run_terminal_cmd">
<parameter name="command">cd server/plugins/sh-shp-llm && git status --porcelain