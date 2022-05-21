using DataCenter.Data.Dtos;
using DataCenter.Data.Enums;
using DataCenter.Data.Models;
using DataCenter.Infrastructure.Providers;
using DataCenter.Infrastructure.Providers.Interfaces;
using DataCenter.Infrastructure.Services.Interfaces;

using System.Threading.Tasks;

namespace DataCenter.Infrastructure.Services
{
    public class WebService : IWebService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        private readonly IUsersService usersService;

        public WebService(
            DatabaseProvider databaseProvider,
            UsersService usersService)
        {
            this.databaseProvider = databaseProvider;
            this.usersService = usersService;
        }

        public async Task<UserWebProfileDto> GetUserProfile(string unitId, string userName)
        {
            BankAccount bankAccount = await GetBankAccount(unitId, userName);

            if (bankAccount == null)
            {
                return null;
            }

            UserDto userDto = await usersService.GetUserDto(userName);

            return CreateUserWebProfileDto(userDto, bankAccount);
        }

        public async Task<string> GetPassword(string userName)
        {
            User user = await usersService.GetUser(userName);

            return user?.Password;
        }

        private UserWebProfileDto CreateUserWebProfileDto(UserDto userDto, BankAccount bankAccount)
        {
            return new UserWebProfileDto
            {
                FullName = userDto.FullName,
                Privilege = GetUserPrivilege(userDto),
                PhoneNumber = userDto.PhoneNumber,
                MoneySummary = bankAccount.Cash + bankAccount.Debit + bankAccount.Credit,
                MinutesPlayed = userDto.MinutesPlayed,
                CreatedDate = userDto.CreatedDate
            };
        }

        private async Task<BankAccount> GetBankAccount(string unitId, string userName)
        {
            return await databaseProvider.SingleOrDefaultAsync<BankAccount>(
                bankAccount =>
                    bankAccount.Name == userName.ToLower() &&
                    bankAccount.UnitId == unitId
                );
        }

        private UserPrivilege GetUserPrivilege(UserDto userDto)
        {
            if (userDto.Vip)
            {
                return UserPrivilege.Vip;
            }

            if (userDto.Builder)
            {
                return UserPrivilege.Builder;
            }

            if (userDto.Realtor)
            {
                return UserPrivilege.Realtor;
            }

            if (userDto.Administrator)
            {
                return UserPrivilege.Administrator;
            }

            return UserPrivilege.None;
        }
    }
}
