# SSH Key Setup Guide for Hostinger

## 🔑 Creating SSH Key on Windows

### Method 1: Using Windows PowerShell (Recommended)

#### Step 1: Open PowerShell

Press `Win + X` and select "Windows PowerShell" or "Terminal"

#### Step 2: Generate SSH Key

```powershell
# Generate ED25519 key (most secure and modern)
ssh-keygen -t ed25519 -C "your_email@example.com"
```

**You'll see:**
```
Generating public/private ed25519 key pair.
Enter file in which to save the key (C:\Users\YourName/.ssh/id_ed25519):
```

**Press Enter** to accept default location.

**You'll see:**
```
Enter passphrase (empty for no passphrase):
```

**Press Enter twice** (for no passphrase) OR enter a secure passphrase if you want extra security.

**Output:**
```
Your identification has been saved in C:\Users\YourName/.ssh/id_ed25519
Your public key has been saved in C:\Users\YourName/.ssh/id_ed25519.pub
The key fingerprint is:
SHA256:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx your_email@example.com
```

✅ **Key created successfully!**

#### Step 3: Copy Your Public Key

```powershell
# Display and copy the public key
cat ~\.ssh\id_ed25519.pub
```

**OR** use this to copy directly to clipboard:

```powershell
# Copy to clipboard
Get-Content ~\.ssh\id_ed25519.pub | Set-Clipboard
```

**Output looks like:**
```
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx your_email@example.com
```

**Copy this entire line** (starts with `ssh-ed25519`, ends with your email)

---

### Method 2: Using PuTTY (Alternative)

If you prefer PuTTY:

#### Step 1: Download PuTTYgen

Download from: https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html

#### Step 2: Generate Key

1. Open **PuTTYgen**
2. Click **Generate**
3. Move mouse randomly in the blank area (generates randomness)
4. Once generated, you'll see the public key in the text box

#### Step 3: Save Keys

1. **Public key**: Copy from the text box at top
2. **Private key**: Click "Save private key" (save as `.ppk` file)
3. Click "Conversions" → "Export OpenSSH key" (save for terminal use)

---

## 🔐 Adding SSH Key to Hostinger

You're already on the right page! Here's what to do:

### Step 1: Paste Public Key

In the Hostinger "Add SSH Key" dialog:

1. **SSH key content** field: Paste your public key
   - Should start with `ssh-ed25519` or `ssh-rsa`
   - Should end with your email
   - Should be ONE LONG LINE (no line breaks)

2. **Name** field: Give it a descriptive name
   - Example: "Windows Laptop 2025"
   - Example: "Sam Work Computer"

### Step 2: Save

Click **Save** button (purple button on bottom right)

### Step 3: Verify

You should see your key listed in the SSH Keys tab.

---

## ✅ Testing SSH Connection

After adding the key to Hostinger:

### From PowerShell:

```powershell
# Test connection
ssh root@srv082055.hstgr.cloud
```

**First time connecting, you'll see:**
```
The authenticity of host 'srv082055.hstgr.cloud' can't be established.
ED25519 key fingerprint is SHA256:xxxxxxxxxxxxxxxxxxx
Are you sure you want to continue connecting (yes/no/[fingerprint])?
```

Type `yes` and press Enter.

**If successful, you'll see:**
```
Welcome to Ubuntu...
root@srv082055:~#
```

✅ **You're connected!**

---

## 🚨 Troubleshooting

### Issue: "Permission denied (publickey)"

**Cause:** Key not found or wrong permissions

**Solution 1:** Verify key location
```powershell
ls ~\.ssh\
```

Should show:
- `id_ed25519` (private key)
- `id_ed25519.pub` (public key)

**Solution 2:** Specify key explicitly
```powershell
ssh -i ~/.ssh/id_ed25519 root@srv082055.hstgr.cloud
```

**Solution 3:** Check if key is in SSH agent
```powershell
# Start SSH agent
Start-Service ssh-agent

# Add key to agent
ssh-add ~\.ssh\id_ed25519
```

### Issue: "Could not open a connection to your authentication agent"

**Solution:**
```powershell
# Enable and start SSH agent service
Set-Service ssh-agent -StartupType Automatic
Start-Service ssh-agent

# Add key
ssh-add ~\.ssh\id_ed25519
```

### Issue: Key file has wrong permissions

**Solution:**
```powershell
# Fix permissions (Windows 10/11)
icacls ~\.ssh\id_ed25519 /inheritance:r
icacls ~\.ssh\id_ed25519 /grant:r "$($env:USERNAME):(R)"
```

### Issue: "ssh-keygen is not recognized"

**Solution:** Install OpenSSH Client

1. Open **Settings** → **Apps** → **Optional Features**
2. Click **Add a feature**
3. Search for "OpenSSH Client"
4. Install it
5. Restart PowerShell

---

## 📋 Quick Reference

### Generate New Key
```powershell
ssh-keygen -t ed25519 -C "your_email@example.com"
```

### View Public Key
```powershell
cat ~\.ssh\id_ed25519.pub
```

### Copy Public Key to Clipboard
```powershell
Get-Content ~\.ssh\id_ed25519.pub | Set-Clipboard
```

### Connect to Hostinger
```powershell
ssh root@srv082055.hstgr.cloud
```

### Specify Custom Key
```powershell
ssh -i ~/.ssh/custom_key root@srv082055.hstgr.cloud
```

---

## 🔒 Security Best Practices

### 1. Use Passphrase (Optional but Recommended)

When generating key, enter a strong passphrase:
- Adds extra layer of security
- Protects key if laptop is stolen
- Can use SSH agent to avoid typing it repeatedly

### 2. Keep Private Key Secure

**Never share your private key** (`id_ed25519` - without .pub)
- Only share the PUBLIC key (`id_ed25519.pub`)
- Don't commit private keys to Git
- Don't email private keys

### 3. Use Different Keys for Different Servers (Optional)

```powershell
# Generate key with custom name
ssh-keygen -t ed25519 -f ~/.ssh/hostinger_key -C "hostinger@example.com"

# Connect using specific key
ssh -i ~/.ssh/hostinger_key root@srv082055.hstgr.cloud
```

### 4. Backup Your Keys

Copy `.ssh` folder to secure backup location:
```powershell
# Backup to external drive
Copy-Item -Recurse ~\.ssh\ E:\Backups\ssh_keys\
```

---

## 🎯 Next Steps After SSH Setup

Once SSH key is working:

1. **Deploy WHS5 Application**
   ```powershell
   ssh root@srv082055.hstgr.cloud
   ```

2. **Run Deployment Script**
   ```bash
   curl -o deploy.sh https://raw.githubusercontent.com/Samilrotech/WHS/master/deploy-to-hostinger.sh
   chmod +x deploy.sh
   sudo bash deploy.sh
   ```

3. **Follow deployment guide** in `DEPLOYMENT_QUICKSTART.md`

---

## 📞 Need More Help?

**Official Hostinger SSH Guide:**
https://support.hostinger.com/en/articles/1583245-how-to-connect-to-vps-using-ssh

**Windows OpenSSH Documentation:**
https://learn.microsoft.com/en-us/windows-server/administration/openssh/openssh_install_firstuse

**Your Deployment Guides:**
- `DEPLOYMENT_QUICKSTART.md` - Quick deployment
- `HOSTINGER_DEPLOYMENT.md` - Complete manual
- `DEPLOYMENT_READY.md` - Next steps

---

## ✅ Summary

**What You Need to Do:**

1. Open PowerShell
2. Run: `ssh-keygen -t ed25519 -C "your_email@example.com"`
3. Press Enter 3 times (accept defaults)
4. Run: `cat ~\.ssh\id_ed25519.pub`
5. Copy the entire output
6. Paste into Hostinger SSH Key form
7. Click Save
8. Test: `ssh root@srv082055.hstgr.cloud`

**That's it!** 🎉
