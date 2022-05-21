using AutoMapper;

using DataCenter.Data.Dtos;
using DataCenter.Data.Models;
using DataCenter.Infrastructure.Providers;
using DataCenter.Infrastructure.Providers.Interfaces;
using DataCenter.Infrastructure.Services.Audit;
using DataCenter.Infrastructure.Services.Audit.Interfaces;
using DataCenter.Infrastructure.Services.Interfaces;
using DataCenter.Utilities;

using System;
using System.Threading.Tasks;

namespace DataCenter.Infrastructure.Services
{
    public class UsersService : IUsersService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IDateTimeProvider dateTimeProvider;

        private readonly IBanRecordsService banRecordsService;

        private readonly IExecutedCommandsAuditService executedCommandsAuditService;

        private readonly IChatMessagesAuditService chatMessagesAuditService;

        private readonly IUserTrafficAuditService userTrafficAuditService;

        private readonly IMapper mapper;

        public UsersService(
            DatabaseProvider databaseProvider,
            DateTimeProvider dateTimeProvider,
            BanRecordsService banRecordsService,
            ExecutedCommandsAuditService executedCommandsAuditService,
            ChatMessagesAuditService chatMessagesAuditService,
            UserTrafficAuditService userTrafficAuditService,
            Mapper mapper)
        {
            this.databaseProvider = databaseProvider;
            this.dateTimeProvider = dateTimeProvider;

            this.banRecordsService = banRecordsService;

            this.executedCommandsAuditService = executedCommandsAuditService;
            this.chatMessagesAuditService = chatMessagesAuditService;
            this.userTrafficAuditService = userTrafficAuditService;

            this.mapper = mapper;
        }

        public Task<bool> Exist(string userName)
        {
            return databaseProvider.AnyAsync<User>(u => u.Name == userName);
        }

        public async Task<User> GetUser(string userName)
        {
            User user = await databaseProvider.SingleOrDefaultAsync<User>(u => u.Name.ToLower() == userName.ToLower());

            await banRecordsService.UpdateBanStatus(user);

            return user;
        }

        public async Task<User> GetUser(int userId)
        {
            User user = await databaseProvider.FindPrimary<User>(userId);

            await banRecordsService.UpdateBanStatus(user);

            return user;
        }

        public async Task<UserSettings> GetUserSettings(string unitId, string userName)
        {
            return await databaseProvider.SingleOrDefaultAsync<UserSettings>(
                settings => settings.UnitId == unitId &&
                settings.Name.ToLower() == userName.ToLower());
        }

        public async Task<UserDto> GetUserDto(string userName)
        {
            User user = await GetUser(userName);
            return mapper.Map<UserDto>(user);
        }

        public async Task<UserSettingsDto> GetUserSettingsDto(string unitId, string userName)
        {
            UserSettings settings = await GetUserSettings(unitId, userName);
            UserSettingsDto settingsDto = mapper.Map<UserSettingsDto>(settings);
            return settingsDto;
        }

        public async Task<string> GetPassword(string userName)
        {
            User user = await GetUser(userName);
            return user.Password;
        }

        public async Task<bool> ExistPassword(string userName)
        {
            User user = await GetUser(userName);
            return user.Password != null;
        }

        public async Task SetPassword(string userName, string password)
        {
            User user = await GetUser(userName);
            user.Password = password;
            databaseProvider.Update(user);
            await databaseProvider.CommitAsync();
        }

        public async Task ResetPassword(string userName)
        {
            await SetPassword(userName, null);
        }

        public async Task<UserDto> CreateInternal(string unitId, string userName)
        {
            await ValidateIsUserExist(userName);

            User user = GetDefaultUserTemplate(userName);
            UserSettings settings = GetDefaultUserSettingsTemplate(unitId, userName);
            await databaseProvider.CreateAsync(user);
            await databaseProvider.CreateAsync(settings);
            await databaseProvider.CommitAsync();

            return mapper.Map<UserDto>(user);
        }

        public async Task Update(UserDto userDto)
        {
            User user = await GetUser(userDto.Name);

            user = ObjectComparer.Merge(user, userDto, 
                    u => u.Id,
                    u => u.Name,
                    u => u.MinutesPlayed,
                    u => u.JoinedDate,
                    u => u.LeftDate,
                    u => u.CreatedDate,
                    u => u.UpdatedDate
                );
            
            databaseProvider.Update(user);
            await databaseProvider.CommitAsync();
        }

        public async Task UpdateSettings(string unitId, UserSettingsDto settingsDto)
        {
            UserSettings settings = await GetUserSettings(unitId, settingsDto.Name);

            settings = ObjectComparer.Merge(settings, settingsDto,
                    u => u.Id,
                    u => u.Name
                );

            databaseProvider.Update(settings);
            await databaseProvider.CommitAsync();
        }

        public async Task UpdateJoinStatus(string unitId, string userName)
        {
            User user = await GetUser(userName);
            user.JoinedDate = dateTimeProvider.Now;

            databaseProvider.Update(user);

            await userTrafficAuditService.SaveUserJoinAttempt(unitId, userName);
        }

        public async Task UpdateQuitStatus(string unitId, string userName)
        {
            User user = await GetUser(userName);

            // TODO: Fix needed
            if (user.JoinedDate.Year == 1)
            {
                return;
            }

            user.LeftDate = dateTimeProvider.Now;
            user.MinutesPlayed += GetMinutesLeft(user.JoinedDate, user.LeftDate);

            databaseProvider.Update(user);

            await userTrafficAuditService.SaveUserQuitAttempt(unitId, userName);
        }

        public async Task SaveExecutedCommandAuditRecord(string unitId, string userName, string command)
        {
            await executedCommandsAuditService.SaveExecutedCommandAuditRecord(unitId, userName, command);
        }

        public async Task SaveChatMessageAuditRecord(string unitId, string userName, string message)
        {
            await chatMessagesAuditService.SaveChatMessageAuditRecord(unitId, userName, message);
        }

        private int GetMinutesLeft(DateTime joinedDate, DateTime leftDate)
        {
            return (int) (leftDate - joinedDate).TotalMinutes;
        }

        private string CreateFullName(string userName)
        {
            return userName.Replace('_', ' ');
        }

        private User GetDefaultUserTemplate(string userName)
        {
            return new User
            {
                Name = userName,
                FullName = CreateFullName(userName),
                Bonus = 3,
                MinutesPlayed = 0,
                Vip = false,
                Administrator = false,
                Builder = false,
            };
        }

        private UserSettings GetDefaultUserSettingsTemplate(string unitId, string userName)
        {
            return new UserSettings
            {
                UnitId = unitId,
                Name = userName,
            };
        }

        private async Task ValidateIsUserExist(string userName)
        {
            if (await Exist(userName))
            {
                throw new InvalidOperationException("User already exists");
            }
        }
    }
}
