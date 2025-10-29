# 🔍 Get Hostinger Server Information

I need the correct server connection details to deploy WHS5.

## What I Need From Hostinger Panel

Please look at your Hostinger VPS panel and provide:

### 1. Server IP Address
Look for:
- **Main VPS page** → "IP Address"
- Or **Overview** → Shows IP like `123.456.789.012`

### 2. SSH Port
Look for:
- **Settings** → **SSH Port** (usually 22, but might be custom)
- Or in the SSH connection string shown

### 3. SSH Connection String
On the VPS overview page, there's usually a connection string like:
```
ssh root@123.456.789.012 -p 22
```

## Where to Find This

In your Hostinger panel:

1. Go to **VPS** section (you're already there)
2. Click on your VPS (srv082055)
3. Look at the **Overview** tab
4. Find:
   - **IP Address**: xxx.xxx.xxx.xxx
   - **SSH Port**: Usually 22 or shown in connection instructions
   - **SSH Access** section with connection command

## Example of What It Looks Like

You'll see something like:
```
IP Address: 123.45.67.89
SSH Port: 22
Connection: ssh root@123.45.67.89 -p 22
```

## Quick Test

Once you have the IP and port, you can test manually:

```powershell
ssh -i D:\WHS5\hostinger_whs5_key -p PORT root@IP_ADDRESS
```

Replace:
- `PORT` with the SSH port number
- `IP_ADDRESS` with the server IP

---

## Alternative: Use Hostinger's Built-in Terminal

If SSH is not accessible yet:

1. In Hostinger panel, find **"Web Terminal"** or **"Console"** button
2. Click it to open browser-based terminal
3. You can run deployment commands directly there!

In the web terminal:
```bash
# Download deployment script
curl -o deploy.sh https://raw.githubusercontent.com/Samilrotech/WHS/master/deploy-to-hostinger.sh

# Make executable
chmod +x deploy.sh

# Run it
sudo bash deploy.sh
```

---

## Tell Me

Please provide either:

**Option A:** Server details
- IP Address: `_______________`
- SSH Port: `_______________`

**Option B:** Use web terminal
- "I'll use the web terminal in Hostinger panel"

Then I can continue with the deployment! 🚀
