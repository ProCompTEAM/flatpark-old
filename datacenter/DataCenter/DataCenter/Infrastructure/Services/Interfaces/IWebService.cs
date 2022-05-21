using DataCenter.Data.Dtos;
using System.Threading.Tasks;

namespace DataCenter.Infrastructure.Services.Interfaces
{
    public interface IWebService
    {
        Task<UserWebProfileDto> GetUserProfile(string unitId, string userName);

        Task<string> GetPassword(string userName);
    }
}
