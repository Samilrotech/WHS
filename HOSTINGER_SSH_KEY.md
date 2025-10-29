# 🔑 WHS5 Hostinger SSH Key

## Your SSH Key (Generated: 2025-01-29)

### ✅ Public Key (Add this to Hostinger)

**Copy this ENTIRE line below:**

```
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAII6Vb2Bkf41/t0aEoWcusbMnQHECM0H6tp4DgppTqCrz whs5-hostinger@rotechrural.com.au
```

---

## 📋 How to Add to Hostinger

You're on the right page already! Just:

### Step 1: Copy the Public Key Above

Select and copy the ENTIRE line starting with `ssh-ed25519`

### Step 2: Paste into Hostinger Form

In the **"Add SSH Key"** dialog you have open:

1. **SSH key content** field:
   - Paste: `ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAII6Vb2Bkf41/t0aEoWcusbMnQHECM0H6tp4DgppTqCrz whs5-hostinger@rotechrural.com.au`

2. **Name** field:
   - Type: `WHS5 Deployment Key`

3. Click **Save** (purple button)

---

## 🔌 Connecting to Hostinger

After adding the key, connect from your Windows machine:

```powershell
ssh -i D:\WHS5\hostinger_whs5_key root@srv082055.hstgr.cloud
```

**First time:** Type `yes` when asked about authenticity

**If successful:** You'll see `root@srv082055:~#`

---

## 🚀 Quick Deployment Commands

Once connected, run:

```bash
# Download deployment script
curl -o deploy.sh https://raw.githubusercontent.com/Samilrotech/WHS/master/deploy-to-hostinger.sh

# Make executable
chmod +x deploy.sh

# Run deployment
sudo bash deploy.sh
```

---

## 📁 Key Files Location

**Private Key** (keep secret): `D:\WHS5\hostinger_whs5_key`
**Public Key** (share with Hostinger): `D:\WHS5\hostinger_whs5_key.pub`

⚠️ **IMPORTANT:** Never share or commit the private key (file without `.pub`)

---

## ✅ Quick Test Connection

```powershell
# From PowerShell or Git Bash
ssh -i D:\WHS5\hostinger_whs5_key root@srv082055.hstgr.cloud
```

If you see the Ubuntu welcome message and `root@srv082055:~#` prompt, you're connected! 🎉

---

## 🔒 Security Notes

- ✅ Key is ED25519 (most secure modern algorithm)
- ✅ 256-bit encryption
- ✅ No passphrase (for automation)
- ✅ Unique fingerprint: `SHA256:2yUU90SkBzyslA61M2awjJAO5Pj0KTsL875ps2Lq3W8`

---

## 📞 Next Steps

1. ✅ Add key to Hostinger (paste the public key above)
2. ✅ Test SSH connection
3. ✅ Run deployment script
4. ✅ Configure DNS for whs.rotechrural.com.au
5. ✅ Access your application!

**Full deployment guide:** See `DEPLOYMENT_QUICKSTART.md`
