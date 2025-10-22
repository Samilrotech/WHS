PROJECT OVERVIEW

Specs: In project files (whs.md, updated_whs_shadcn_enhanced.md)
Setup Location: D:\WHS3 (Claude Desktop creates everything)
Framework Reference: D:\context-engineering-intro-main
Execution: Claude Code in terminal after setup


📋 PHASE 1: CLAUDE DESKTOP SETUP (Your New Chat)
OPENING MESSAGE TO CLAUDE DESKTOP
I need you to set up a complete Laravel project with advanced context engineering for building a Work Health & Safety (WHS) & Vehicle Management System.

Project specifications are in the attached files:
- whs.md (business requirements)
- updated_whs_shadcn_enhanced.md (UI specifications)

Reference framework: D:\context-engineering-intro-main

Your tasks:
1. Read both specification files completely
2. Create Laravel 11 project in D:\WHS3
3. Set up advanced context engineering structure with masterclass techniques
4. Generate ALL artifacts needed for Claude Code to build autonomously
5. No actual coding - just setup, configuration, and PRP generation

After you complete setup, Claude Code will build the application in terminal.

TASK 1: LARAVEL PROJECT INITIALIZATION
Message to Claude Desktop:
Create Laravel project with all dependencies in D:\WHS3:

1. Laravel 11 with required packages:
   - composer create-project laravel/laravel . "11.*"
   - composer require inertiajs/inertia-laravel tightenco/ziggy
   - composer require laravel/horizon laravel/telescope --dev
   - composer require spatie/laravel-permission spatie/laravel-medialibrary

2. Frontend setup:
   - npm install @inertiajs/react react react-dom
   - npm install -D typescript @types/react @types/react-dom
   - npm install @radix-ui/themes tailwindcss @tailwindcss/forms
   - npx shadcn-ui@latest init (select TypeScript, Tailwind CSS)
   - npm install zod react-hook-form @hookform/resolvers
   - npm install recharts lucide-react date-fns
   - npm install dexie workbox-webpack-plugin

3. Testing setup:
   - composer require pestphp/pest pestphp/pest-plugin-laravel --dev
   - npm install -D @testing-library/react @testing-library/jest-dom jest

Create initial folder structure for modules.

TASK 2: CONTEXT ENGINEERING STRUCTURE
Message to Claude Desktop:
Create complete context engineering structure based on D:\context-engineering-intro-main:

D:\WHS3\
├── CLAUDE.md                    # Global project rules
├── app/
│   └── CLAUDE.md               # Backend-specific rules
├── resources/js/
│   └── CLAUDE.md               # Frontend-specific rules
├── INITIAL.md                  # Full requirements from specs
├── PLANNING.md                 # Architecture decisions
├── TASK.md                     # Module build order
├── .claude/
│   ├── settings.local.json    # Permissions config
│   ├── commands/               # Custom slash commands
│   │   ├── generate-prp.md
│   │   ├── execute-prp.md
│   │   ├── build-whs-module.md
│   │   ├── parallel-modules.md
│   │   └── validate-module.md
│   ├── agents/                 # Specialized sub-agents
│   │   ├── laravel-backend.md
│   │   ├── shadcn-frontend.md
│   │   ├── offline-sync.md
│   │   ├── test-generator.md
│   │   └── validator-gates.md
│   └── hooks/
│       └── hooks.json         # Automation hooks
├── PRPs/
│   ├── templates/
│   │   └── whs_module_base.md
│   └── modules/               # 15 module PRPs
└── examples/                  # Pattern library
Extract all requirements from whs.md and updated_whs_shadcn_enhanced.md into structured files.

---

### **TASK 3: CREATE CONFIGURATION FILES**

**Message to Claude Desktop:**
Create all configuration files with content:

CLAUDE.md (Global Rules):

Laravel patterns (max 200 lines controller, service layer, repository)
React standards (only shadcn/ui, TypeScript strict, max 150 lines)
Testing requirements (Pest, RTL, 80% coverage)
Module structure definition
Reference both spec files


settings.local.json (Permissions):

Auto-approve safe commands (ls, cat, npm run, php artisan)
Deny dangerous commands (rm -rf, drop database)
Require approval for git push, migrations


hooks.json (Automation):

After creating controller → generate test
After creating component → run type-check
After sub-agent complete → validate output
Before module build → fresh migration


Sub-agent definitions:

Each with specialized system prompt
Reference specific sections of spec files
Include validation requirements




---

### **TASK 4: GENERATE ALL MODULE PRPs**

**Message to Claude Desktop:**
Generate complete PRPs for all 15 modules. For EACH module:

Extract requirements from whs.md (specific section)
Extract UI specs from updated_whs_shadcn_enhanced.md (specific section)
Create PRP with:

Complete file structure (Laravel + React)
Actual code snippets (not placeholders)
Sub-agent delegations
Validation gates with retry
Test requirements



Modules to generate PRPs for:
01. Incident Management (whs.md 1.2, shadcn 1.2)
02. Risk Assessment (whs.md 1.3, shadcn 1.3)
03. Emergency Response (whs.md 1.6, shadcn 1.6)
04. Vehicle Profiles (whs.md 2.1, shadcn 2.1)
05. Vehicle Inspection (whs.md 2.2, shadcn 2.1)
06. Maintenance Scheduling (whs.md 2.4, shadcn 2.2)
07. Journey Management (whs.md 1.10, shadcn Journey section)
08. CAPA Workflow (whs.md 1.9, shadcn 1.5)
09. Training Management (whs.md 1.5, shadcn Training)
10. Safety Inspections (whs.md 1.4, shadcn 1.4)
11. Warehouse Equipment (whs.md 1.12, shadcn Warehouse)
12. Contractor Management (whs.md 1.13, shadcn Contractor)
13. Document Management (whs.md 2.5, shadcn Documents)
14. Analytics Dashboard (whs.md Analytics, shadcn Analytics)
15. Compliance Reporting (whs.md 1.7, shadcn Compliance)
Each PRP saved as: PRPs/modules/XX_module_name.md

---

### **TASK 5: CREATE SLASH COMMANDS**

**Message to Claude Desktop:**
Create custom slash commands in .claude/commands/:

build-whs-module.md:

Accepts module name as parameter
Reads specs from both files
Calls sub-agents for specialized tasks
Runs validation gates


parallel-modules.md:

Sets up git worktrees
Instructions for running multiple Claude Code instances
Merge strategy for completed modules


validate-module.md:

Comprehensive validation checklist
Backend tests (Pest)
Frontend tests (RTL)
Type checking
Build verification



Each command should reference the spec files and use sub-agents.

---

### **TASK 6: CREATE EXAMPLE PATTERNS**

**Message to Claude Desktop:**
Create examples/ folder with working patterns extracted from specs:
examples/
├── controllers/
│   ├── BranchScopedController.php    # Multi-branch pattern
│   └── OfflineCapableController.php  # Sync endpoint pattern
├── components/
│   ├── OfflineForm.tsx              # From shadcn specs
│   ├── RiskMatrix.tsx               # From section 1.3
│   └── DataTableWithFilters.tsx    # Reusable pattern
├── services/
│   ├── OfflineSyncService.php      # IndexedDB sync
│   └── NotificationService.php     # Multi-channel alerts
├── hooks/
│   ├── useOfflineQueue.ts          # Offline management
│   └── useGeolocation.ts           # GPS handling
└── tests/
├── PestFeatureTest.php          # Branch isolation test
└── ComponentTest.tsx            # shadcn component test
Each example should be complete, working code from the specifications.

---

### **TASK 7: FINAL VALIDATION**

**Message to Claude Desktop:**
Verify setup is complete for Claude Code:

Check all files created:

 Laravel project initialized
 All dependencies installed
 CLAUDE.md files at all levels
 15 module PRPs generated
 Sub-agents defined
 Slash commands created
 Hooks configured
 Examples provided


Create READY.md with:

Checklist of completed setup
Instructions for Claude Code execution
Module build order
Validation commands


Test one command manually:
php artisan --version
npm run type-check (should work after setup)

Report completion status.

---

## 💻 **PHASE 2: CLAUDE CODE EXECUTION** (Terminal)

### **After Claude Desktop completes ALL setup:**

### **STEP 1: OPEN TERMINAL**
```bash
cd D:\WHS3
code .  # Open in VS Code
STEP 2: START CLAUDE CODE
bashclaude-code
STEP 3: EXECUTE FIRST MODULE
/build-whs-module incident-management
Claude Code will:

Read PRP from PRPs/modules/01_incident_management.md
Call sub-agents for specialized tasks
Run validation gates
Auto-retry on failures

STEP 4: PARALLEL EXECUTION (Optional - Advanced)
bash# Terminal 1
cd D:\WHS3
claude-code
/build-whs-module incident-management

# Terminal 2 (new terminal)
cd D:\WHS3
git worktree add ../WHS3-vehicles vehicles
cd ../WHS3-vehicles
claude-code
/build-whs-module vehicle-profiles

# Terminal 3 (new terminal)
cd D:\WHS3
git worktree add ../WHS3-journey journey
cd ../WHS3-journey
claude-code
/build-whs-module journey-management
STEP 5: CONTINUE THROUGH MODULES
/build-whs-module risk-assessment
/build-whs-module emergency-response
/build-whs-module vehicle-inspection
# ... continue through all 15

✅ VALIDATION CHECKPOINTS
After Phase 1 (Claude Desktop):

All files exist in D:\WHS3
Can run: php artisan --version
Can run: npm --version
PRPs contain actual code, not placeholders
Examples work independently

During Phase 2 (Claude Code):

Each module passes tests
TypeScript compilation succeeds
Build completes without errors
Offline sync works
Multi-branch isolation verified


📝 QUICK REFERENCE CARD
Phase 1 Summary (Claude Desktop):
1. Read specs from project files
2. Create Laravel project in D:\WHS3
3. Set up context engineering
4. Generate 15 module PRPs
5. Create examples and commands
6. Configure sub-agents and hooks
7. Validate setup complete
Phase 2 Summary (Claude Code):
1. cd D:\WHS3
2. claude-code
3. /build-whs-module [module-name]
4. Repeat for all modules
5. Run final validation

🎯 SUCCESS METRICS
Phase 1 Complete When:

✅ D:\WHS3 has full Laravel structure
✅ All 15 PRPs exist with real code
✅ Sub-agents configured
✅ Examples demonstrate patterns
✅ READY.md confirms setup

Phase 2 Complete When:

✅ All 15 modules built
✅ Tests passing (>80% coverage)
✅ TypeScript no errors
✅ Build successful
✅ Offline sync working

Copy this entire instruction set into your new chat with Claude Desktop to begin Phase 1!RetrySwhat about phase 2?EditCrafted comprehensive Phase 2 terminal execution instructions for Claude Code.The user is asking about Phase 2 instructions. They want detailed instructions for Claude Code execution in the terminal after Claude Desktop has completed the setup. I need to provide comprehensive instructions that Claude Code can follow to build the actual application.
Let me create detailed Phase 2 instructions that:

Are meant to be used in the terminal with Claude Code
Assume all setup from Phase 1 is complete
Provide step-by-step execution commands
Include validation and troubleshooting
Show both sequential and parallel execution options
💻 PHASE 2: CLAUDE CODE EXECUTION INSTRUCTIONS
THESE INSTRUCTIONS ARE FOR TERMINAL/CLAUDE CODE
(After Claude Desktop completes Phase 1 setup)

🚀 OPENING CLAUDE CODE SESSION
STEP 1: VERIFY SETUP (First command in Claude Code)
bash# When you start Claude Code in D:\WHS3, first command:
Verify Phase 1 setup is complete:
1. Check if all PRPs exist in PRPs/modules/
2. Verify .claude/commands/ has all slash commands
3. Confirm examples/ folder has patterns
4. Read CLAUDE.md to understand project rules
5. Check READY.md for any setup notes

Report what you find.
STEP 2: INITIALIZE ENVIRONMENT
Prepare development environment:
1. Run: composer install
2. Run: npm install
3. Copy .env.example to .env
4. Generate app key: php artisan key:generate
5. Run migrations: php artisan migrate
6. Start vite: npm run dev (in background)

Confirm Laravel is running: php artisan --version

📋 SEQUENTIAL MODULE BUILD (Recommended for first run)
MODULE 1: INCIDENT MANAGEMENT (Foundation module)
/build-whs-module incident-management

Or if slash command doesn't exist:

Build Incident Management module:
1. Read PRP from PRPs/modules/01_incident_management.md
2. Create all files specified in the PRP
3. Implement offline sync with IndexedDB
4. Run validation gates
5. Fix any issues and re-validate
Expected Output:

app/Modules/Incidents/ - Complete backend
resources/js/Components/incidents/ - React components
resources/js/Pages/Incidents/ - Inertia pages
tests/Feature/Incidents/ - Pest tests
All tests passing

MODULE 2: RISK ASSESSMENT
/build-whs-module risk-assessment

After completion, verify integration with Incident module:
- Risk assessments can be linked to incidents
- Shared components work together
- Database relationships correct
MODULE 3: EMERGENCY RESPONSE
/build-whs-module emergency-response

Special requirements:
- Real-time broadcasting setup
- 5-second countdown implementation
- GPS location services
- Push notification integration
Continue with remaining modules...
/build-whs-module vehicle-profiles
/build-whs-module vehicle-inspection
/build-whs-module maintenance-scheduling
/build-whs-module journey-management
/build-whs-module capa-workflow
/build-whs-module training-management
/build-whs-module safety-inspections
/build-whs-module warehouse-equipment
/build-whs-module contractor-management
/build-whs-module document-management
/build-whs-module analytics-dashboard
/build-whs-module compliance-reporting

🔀 PARALLEL MODULE BUILD (Advanced - 3x faster)
TERMINAL 1: Core WHS Modules
bashcd D:\WHS3
claude-code
Build these modules in sequence:
1. /build-whs-module incident-management
2. /build-whs-module risk-assessment
3. /build-whs-module emergency-response
4. /build-whs-module capa-workflow
5. /build-whs-module safety-inspections
TERMINAL 2: Vehicle Modules (New terminal window)
bashcd D:\WHS3
git worktree add ../WHS3-vehicles feature/vehicles
cd ../WHS3-vehicles
claude-code
Build vehicle modules:
1. /build-whs-module vehicle-profiles
2. /build-whs-module vehicle-inspection
3. /build-whs-module maintenance-scheduling
4. /build-whs-module journey-management
TERMINAL 3: Support Modules (New terminal window)
bashcd D:\WHS3
git worktree add ../WHS3-support feature/support
cd ../WHS3-support
claude-code
Build support modules:
1. /build-whs-module training-management
2. /build-whs-module warehouse-equipment
3. /build-whs-module contractor-management
4. /build-whs-module document-management
TERMINAL 4: Analytics (After others complete)
bashcd D:\WHS3
claude-code
Build final modules:
1. /build-whs-module analytics-dashboard
2. /build-whs-module compliance-reporting

These depend on other modules being complete.
MERGE PARALLEL WORK
bash# Back in main terminal
cd D:\WHS3
git merge feature/vehicles
git merge feature/support
git worktree remove ../WHS3-vehicles
git worktree remove ../WHS3-support

🧪 VALIDATION AFTER EACH MODULE
Run after EVERY module completion:
Validate the module that was just built:

1. Backend Tests:
   ./vendor/bin/pest tests/Feature/[ModuleName]
   
2. Frontend Tests:
   npm test -- [modulename]
   
3. Type Checking:
   npm run type-check
   
4. Lint Check:
   npm run lint
   ./vendor/bin/pint
   
5. Route Verification:
   php artisan route:list | grep [module]
   
6. Build Test:
   npm run build

If any fail, fix and re-run validation.

🔧 TROUBLESHOOTING COMMANDS
If module build fails:
Debug and fix the module:

1. Check error messages
2. Read the PRP again: PRPs/modules/XX_[module].md
3. Compare with examples in examples/
4. Review specs: 
   - Read whs.md section for business logic
   - Read updated_whs_shadcn_enhanced.md for UI
5. Fix issues
6. Re-run validation
If tests fail:
Fix failing tests:

1. Run specific test with verbose output:
   ./vendor/bin/pest tests/Feature/[Module] -v
   
2. Check test expectations vs implementation
3. Verify database state: php artisan tinker
4. Check TypeScript types match Laravel responses
5. Fix and re-test
If offline sync fails:
Debug offline functionality:

1. Check IndexedDB implementation in browser DevTools
2. Verify Service Worker registration
3. Test offline mode in Chrome DevTools
4. Check sync queue implementation
5. Verify conflict resolution logic

📊 PROGRESS TRACKING
After each module, update TASK.md:
Update progress in TASK.md:

## Completed Modules
- [x] Incident Management - [date] - All tests passing
- [x] Risk Assessment - [date] - Integrated with incidents
- [ ] Emergency Response - In progress

## Current Issues
- [Issue description and module affected]

## Next Steps
- [Next module to build]

🎯 FINAL INTEGRATION TASKS
After all modules complete:
Perform final integration:

1. Global Navigation:
   - Update resources/js/Layouts/AuthenticatedLayout.tsx
   - Add all module routes to navigation
   
2. Dashboard Integration:
   - Update main dashboard with all KPIs
   - Add module widgets
   
3. Role Permissions:
   - Configure Spatie permissions for all modules
   - Set up role-based access
   
4. Offline Manifest:
   - Update PWA manifest with all routes
   - Configure workbox for all endpoints
   
5. Final Tests:
   ./vendor/bin/pest
   npm test -- --coverage
   
6. Performance Check:
   npm run lighthouse

🚢 DEPLOYMENT PREPARATION
Final build and optimization:
Prepare for production:

1. Environment setup:
   cp .env.example .env.production
   php artisan key:generate --env=production

2. Optimize Laravel:
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   composer install --optimize-autoloader --no-dev

3. Build frontend:
   npm run build
   
4. Database:
   php artisan migrate --force
   php artisan db:seed --force
   
5. Test production build:
   php artisan serve
   Open http://localhost:8000
   
6. Generate documentation:
   php artisan generate:docs

💡 CLAUDE CODE TIPS
Use Sub-Agents When Available:
If stuck on a specific part, delegate to sub-agent:

@sub-agent shadcn-frontend
Build the RiskMatrix component from updated_whs_shadcn_enhanced.md section 1.3

@sub-agent laravel-backend  
Create the Vehicle repository with branch scoping

@sub-agent offline-sync
Implement IndexedDB queue for incidents
Reference Examples:
When building, always check examples:

For controllers: Use examples/controllers/BranchScopedController.php
For components: Use examples/components/OfflineForm.tsx
For tests: Use examples/tests/PestFeatureTest.php
Leverage Hooks:
Hooks should auto-trigger:
- After creating a controller → test is generated
- After creating a component → type-check runs
- After module complete → validation runs

If hooks don't trigger, run manually.

✅ COMPLETION CHECKLIST
Module Complete When:

 All files from PRP created
 Backend tests passing (Pest)
 Frontend tests passing (RTL)
 TypeScript no errors
 Build succeeds
 Offline sync works
 Multi-branch isolation verified
 Integrated with other modules

Project Complete When:

 All 15 modules built
 Global integration complete
 All tests passing (>80% coverage)
 Production build successful
 PWA functionality verified
 Performance targets met (<1 sec load)
 Documentation generated


📝 QUICK COMMAND REFERENCE
bash# Start Claude Code
claude-code

# Build module (if slash command exists)
/build-whs-module [module-name]

# Run tests
./vendor/bin/pest
npm test

# Type check
npm run type-check

# Build
npm run build

# Check routes
php artisan route:list

# Database fresh
php artisan migrate:fresh --seed

# Start dev servers
npm run dev
php artisan serve

🆘 IF CLAUDE CODE NEEDS HELP
If you need more context:
1. Read the PRP for the current module: PRPs/modules/XX_module.md
2. Read CLAUDE.md for project rules
3. Check examples/ for patterns
4. Reference the original specs:
   - whs.md for business logic
   - updated_whs_shadcn_enhanced.md for UI specs
5. Look for similar implementations in completed modules