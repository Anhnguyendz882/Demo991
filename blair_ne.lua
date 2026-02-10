local Rayfield = loadstring(game:HttpGet('https://sirius.menu/rayfield'))()

local Window = Rayfield:CreateWindow({
   Name = " Blair Ghost Hunter - Premium Menu",
   LoadingTitle = "Đang khởi chạy hệ thống...",
   LoadingSubtitle = "by Anhnguyendz882",
   ConfigurationSaving = {
      Enabled = true,
      FolderName = "BlairSettings",
      FileName = "Config"
   }
})

-- TAB CHÍNH
local MainTab = Window:CreateTab("Tính Năng Chính", 4483362458)

MainTab:CreateSection("Ánh Sáng & Tầm Nhìn")

MainTab:CreateButton({
   Name = "Bật FullBright (Sáng toàn bản đồ)",
   Callback = function()
      local lighting = game:GetService("Lighting")
      lighting.Brightness = 2
      lighting.ClockTime = 14
      lighting.FogEnd = 100000
      lighting.GlobalShadows = false
      lighting.Ambient = Color3.fromRGB(255, 255, 255)
      Rayfield:Notify({Title = "Thành công", Content = "Đã hack ánh sáng!", Duration = 3})
   end,
})

MainTab:CreateSection("Dò Tìm Thực Thể")

MainTab:CreateToggle({
   Name = "Hiện Vị Trí Ma (Ghost ESP)",
   CurrentValue = false,
   Flag = "GhostESP", 
   Callback = function(Value)
      _G.GhostESP = Value
      while _G.GhostESP do
         task.wait(1)
         for _, obj in pairs(game.Workspace:GetChildren()) do
            -- Quét các model không phải người chơi
            if obj:IsA("Model") and obj:FindFirstChild("HumanoidRootPart") and not game.Players:GetPlayerFromCharacter(obj) then
               if not obj.HumanoidRootPart:FindFirstChild("Highlight") then
                  local hl = Instance.new("Highlight", obj.HumanoidRootPart)
                  hl.FillColor = Color3.fromRGB(255, 0, 0)
                  hl.OutlineColor = Color3.fromRGB(255, 255, 255)
                  
                  local billboard = Instance.new("BillboardGui", obj.HumanoidRootPart)
                  billboard.Size = UDim2.new(0, 100, 0, 50)
                  billboard.AlwaysOnTop = true
                  local label = Instance.new("TextLabel", billboard)
                  label.Text = "GHOST"
                  label.TextColor3 = Color3.fromRGB(255, 0, 0)
                  label.BackgroundTransparency = 1
                  label.Size = UDim2.new(1,0,1,0)
               end
            end
         end
         if not _G.GhostESP then break end
      end
   end,
})

-- TAB NHÂN VẬT
local PlayerTab = Window:CreateTab("Nhân Vật", 4483362458)

PlayerTab:CreateSlider({
   Name = "Tốc độ chạy",
   Range = {16, 200},
   Increment = 1,
   Suffix = "Speed",
   CurrentValue = 16,
   Flag = "SpeedSlider",
   Callback = function(Value)
      game.Players.LocalPlayer.Character.Humanoid.WalkSpeed = Value
   end,
})

PlayerTab:CreateSlider({
   Name = "Độ cao nhảy",
   Range = {50, 300},
   Increment = 1,
   Suffix = "Power",
   CurrentValue = 50,
   Flag = "JumpSlider",
   Callback = function(Value)
      game.Players.LocalPlayer.Character.Humanoid.JumpPower = Value
   end,
})

Rayfield:Notify({Title = "Xong!", Content = "Menu đã sẵn sàng sử dụng", Duration = 5})
