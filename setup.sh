#!/bin/bash

# --- CONFIGURATION ---
NEW_USER="deploy"
CUSTOM_SSH_PORT="7589"
SSH_PUBLIC_KEY="ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIA7irVnlvLE7ae29BwsDM/NTM3hmmV/TgJi0xak9yadM omodingmike@gmail.com"

echo "Starting hardened VPS setup..."

# 1. Update & Upgrade System
export DEBIAN_FRONTEND=noninteractive
apt update && apt upgrade -y

# 2. Create User (Check if exists)
if id "$NEW_USER" &>/dev/null; then
  echo "User $NEW_USER already exists. Skipping creation."
else
  echo "Creating user: $NEW_USER"
  useradd -m -s /bin/bash "$NEW_USER"
  passwd -l "$NEW_USER"
  usermod -aG sudo "$NEW_USER"
fi

# 3. Setup SSH (Check if key already present)
mkdir -p /home/"$NEW_USER"/.ssh
if grep -q "$SSH_PUBLIC_KEY" /home/"$NEW_USER"/.ssh/authorized_keys 2>/dev/null; then
  echo "SSH Public Key already authorized for $NEW_USER."
else
  echo "$SSH_PUBLIC_KEY" >> /home/"$NEW_USER"/.ssh/authorized_keys
  echo "SSH Public Key added."
fi
chown -R "$NEW_USER":"$NEW_USER" /home/"$NEW_USER"/.ssh
chmod 700 /home/"$NEW_USER"/.ssh
chmod 600 /home/"$NEW_USER"/.ssh/authorized_keys

# 4. Docker Installation (Check if exists)
if command -v docker &> /dev/null; then
  echo "Docker is already installed."
else
  echo "Installing Docker..."
  apt install docker.io -y
  systemctl enable --now docker
fi

# Add user to docker group if not already a member
if groups "$NEW_USER" | grep &>/dev/null "\bdocker\b"; then
  echo "User $NEW_USER is already in the docker group."
else
  usermod -aG docker "$NEW_USER"
  echo "User $NEW_USER added to docker group."
fi

# 5. Passwordless Docker Sudo (Check if file exists)
if [ -f /etc/sudoers.d/deploy-docker ]; then
  echo "Passwordless Docker sudo rule already exists."
else
  echo "$NEW_USER ALL=(ALL) NOPASSWD: /usr/bin/docker" > /etc/sudoers.d/deploy-docker
  chmod 440 /etc/sudoers.d/deploy-docker
  echo "Passwordless Docker sudo rule created."
fi

# 6. Harden SSH Configuration
echo "Applying SSH hardening..."
# Use -i.bak to keep a backup of the original config
sed -i "s/^#*Port.*/Port $CUSTOM_SSH_PORT/" /etc/ssh/sshd_config
sed -i 's/^#*PermitRootLogin.*/PermitRootLogin no/' /etc/ssh/sshd_config
sed -i 's/^#*PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config
sed -i 's/^#*PubkeyAuthentication.*/PubkeyAuthentication yes/' /etc/ssh/sshd_config

# 7. Firewall (UFW)
if ufw status | grep -q "active"; then
  echo "Firewall is already active."
else
  echo "Configuring Firewall..."
  ufw default deny incoming
  ufw default allow outgoing
  ufw allow "$CUSTOM_SSH_PORT"/tcp
  ufw allow http
  ufw allow https
  echo "y" | ufw enable
fi

# 8. Fail2Ban (Check if jail.local exists)
if [ -f /etc/fail2ban/jail.local ]; then
  echo "Fail2Ban configuration already exists."
else
  echo "Configuring Fail2Ban..."
  apt install fail2ban -y
  cat <<EOM > /etc/fail2ban/jail.local
[sshd]
enabled = true
port = $CUSTOM_SSH_PORT
maxretry = 5
bantime = 1h
EOM
  systemctl restart fail2ban
fi

# 9. Final Cleanup & Restart
systemctl restart ssh

echo "-------------------------------------------------------"
echo "Setup Complete & Verified!"
echo "Login: ssh -p $CUSTOM_SSH_PORT $NEW_USER@$(hostname -I | awk '{print $1}')"
echo "-------------------------------------------------------"